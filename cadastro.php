<?php
session_start();

/* =========================================
   CONEXÃO
========================================= */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'test';

$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

$mensagem = '';
$tipoMensagem = '';

if (!$conn) {
    die('Erro na conexão com o banco.');
}

/* =========================================
   CADASTRAR
========================================= */

if (isset($_POST['cadastrar'])) {

    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {

        $mensagem = 'Preencha todos os campos.';
        $tipoMensagem = 'erro';

    } else {

        // verifica email
        $sql = "SELECT id FROM tb_teste WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {

            $mensagem = 'Este email já está cadastrado.';
            $tipoMensagem = 'erro';

        } else {

            mysqli_stmt_close($stmt);

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $sqlInsert = "INSERT INTO tb_teste(email, senha)
                          VALUES (?, ?)";

            $stmtInsert = mysqli_prepare($conn, $sqlInsert);

            mysqli_stmt_bind_param(
                $stmtInsert,
                "ss",
                $email,
                $senhaHash
            );

            if (mysqli_stmt_execute($stmtInsert)) {

                $_SESSION['ultimo_id'] = mysqli_insert_id($conn);

                $mensagem = 'Usuário cadastrado com sucesso!';
                $tipoMensagem = 'sucesso';

            } else {

                $mensagem = 'Erro ao cadastrar.';
                $tipoMensagem = 'erro';
            }

            mysqli_stmt_close($stmtInsert);
        }
    }
}

/* =========================================
   EXCLUIR
========================================= */

if (isset($_POST['excluir'])) {

    $emailExcluir = trim($_POST['email_excluir'] ?? '');

    if (empty($emailExcluir)) {

        $mensagem = 'Digite um email.';
        $tipoMensagem = 'erro';

    } else {

        $sql = "SELECT id FROM tb_teste WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $emailExcluir);

        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {

            mysqli_stmt_close($stmt);

            $sqlDelete = "DELETE FROM tb_teste WHERE email = ?";

            $stmtDelete = mysqli_prepare($conn, $sqlDelete);

            mysqli_stmt_bind_param(
                $stmtDelete,
                "s",
                $emailExcluir
            );

            if (mysqli_stmt_execute($stmtDelete)) {

                $mensagem = 'Usuário excluído com sucesso!';
                $tipoMensagem = 'sucesso';

            } else {

                $mensagem = 'Erro ao excluir.';
                $tipoMensagem = 'erro';
            }

            mysqli_stmt_close($stmtDelete);

        } else {

            $mensagem = 'Email não encontrado.';
            $tipoMensagem = 'erro';

            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>RELPJAM - Cadastro</title>
    <!-- GOOGLE FONTS -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS -->
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
    <style>
    /* =========================================================
   CAD - CADASTRO
========================================================= */
.cad-body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    min-height: 100vh;
    color: white;
    padding: 40px 20px;
}
/* HEADER */
.cad-header {
    text-align: center;
    margin-bottom: 40px;
}
.cad-logo {
    font-size: 2.7rem;
    color: #00d9a5;
}
.cad-subtitle {
    margin-top: 10px;
    color: #cbd5e1;
    font-size: 1rem;
}
/* CONTAINER */
.cad-container {
    width: 100%;
    max-width: 1100px;
    margin: auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
/* CARD */
.cad-card {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

/* TITLES */

.cad-title {
    color: #00d9a5;
    margin-bottom: 30px;
    font-size: 1.8rem;
}

.cad-danger {
    color: #ef4444;
}

/* FORM */

.cad-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* GROUP */

.cad-group {
    display: flex;
    flex-direction: column;
}

/* LABEL */

.cad-label {
    margin-bottom: 8px;
    color: #e2e8f0;
}

/* INPUT */

.cad-input {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background-color: #f1f5f9;
    font-size: 1rem;
    transition: 0.3s;
}

.cad-input:focus {
    outline: none;
    border: 2px solid #00d9a5;
    box-shadow: 0 0 10px rgba(0,217,165,0.5);
}

/* BUTTON */

.cad-btn {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 10px;
    background-color: #00d9a5;
    color: #0f172a;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.cad-btn:hover {
    background-color: #00b386;
    transform: translateY(-2px);
}

/* BUTTON DANGER */

.cad-btn-danger {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 10px;
    background-color: #ef4444;
    color: white;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.cad-btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-2px);
}

/* RESPONSIVO */

@media(max-width: 900px) {
    .cad-container {
        grid-template-columns: 1fr;
    }
}
@media(max-width: 500px) {
    .cad-card {
        padding: 25px 20px;
    }
    .cad-logo {
        font-size: 2rem;
    }
}
    </style>

</head>

<body class="cad-body">
    <!-- HEADER -->
    <header class="cad-header">
        <p class="cad-subtitle"> Cadastro de Usuário</p>
    </header>
    <!-- CONTAINER -->
    <main class="cad-container">
        <!-- CARD CADASTRO -->
        <section class="cad-card">
            <h2 class="cad-title">Criar Conta</h2>
            <form method="POST" class="cad-form">
                <!-- EMAIL -->
                <div class="cad-group">
                    <label class="cad-label">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        class="cad-input"
                        maxlength="50"
                        placeholder="Digite seu e-mail"
                        required>
                </div>
                <!-- SENHA -->
                <div class="cad-group">
                    <label class="cad-label">Senha </label>
                    <input
                        type="password"
                        name="senha"
                        class="cad-input"
                        maxlength="15"
                        placeholder="Digite sua senha"
                        required>
                </div>
                <!-- BOTÃO -->
                <button
                    type="submit"
                    name="cadastrar"
                    class="cad-btn">Cadastrar
                </button>
            </form>
        </section>
        <!-- CARD EXCLUSÃO -->
        <section class="cad-card">
            <h2 class="cad-title cad-danger">Excluir Usuário</h2>
            <form method="POST" class="cad-form">
                <div class="cad-group">
                    <label class="cad-label">E-mail</label>
                    <input
                        type="email"
                        name="email_excluir"
                        class="cad-input"
                        maxlength="50"
                        placeholder="Digite o e-mail"
                        required>
                </div>
                <button
                    type="submit"
                    name="excluir"
                    class="cad-btn-danger">Excluir Usuário
                </button>
            </form>
        </section>
    </main>
    <!-- ALERTAS -->
    <?php if ($mensagem !== ''): ?>
        <script>
            Swal.fire({
                icon: '<?= $tipoMensagem === "sucesso" ? "success" : "error"; ?>',
                title: '<?= $tipoMensagem === "sucesso" ? "Sucesso" : "Erro"; ?>',
                text: '<?= $mensagem; ?>',
                confirmButtonColor: '#00d9a5'
            });
        </script>
    <?php endif; ?>

</body>
</html>