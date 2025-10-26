// Recuperar Senha Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const recuperarSenhaForm = document.getElementById('recuperarSenhaForm');
    const emailInput = document.getElementById('email');

    // Validação de e-mail
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Limpar erro
    function clearError() {
        document.getElementById('emailError').textContent = '';
        emailInput.classList.remove('error');
    }

    // Mostrar erro
    function showError(message) {
        const error = document.getElementById('emailError');
        emailInput.classList.add('error');
        error.textContent = message;
    }

    // Validação do formulário
    function validateForm() {
        clearError();

        // Validar e-mail
        if (!emailInput.value.trim()) {
            showError('E-mail é obrigatório');
            return false;
        } else if (!validateEmail(emailInput.value)) {
            showError('E-mail inválido');
            return false;
        }

        return true;
    }

    // Submissão do formulário: valida e deixa o POST seguir para o PHP
    recuperarSenhaForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Remover erros ao digitar
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            clearError();
        }
    });

    console.log('Recuperar senha page ready (POST to PHP)');
});
