document.addEventListener('DOMContentLoaded', function () {
    // Verifica se há mensagem de sucesso armazenada
    const mensagemSucesso = localStorage.getItem('cadastroSucesso');
    if (mensagemSucesso) {
        // Exibe a mensagem como um aviso
        alert(mensagemSucesso);

        // Remove a mensagem do armazenamento local após exibir
        localStorage.removeItem('cadastroSucesso');
    }
});
