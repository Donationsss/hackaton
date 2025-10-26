// Login Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    // Toggle mostrar/ocultar senha
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });

    // Validação de e-mail
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Limpar erros
    function clearErrors() {
        document.getElementById('emailError').textContent = '';
        document.getElementById('passwordError').textContent = '';
        emailInput.classList.remove('error');
        passwordInput.classList.remove('error');
    }

    // Mostrar erro
    function showError(inputId, errorId, message) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        input.classList.add('error');
        error.textContent = message;
    }

    // Validação do formulário
    function validateForm() {
        clearErrors();
        let isValid = true;

        // Validar e-mail
        if (!emailInput.value.trim()) {
            showError('email', 'emailError', 'E-mail é obrigatório');
            isValid = false;
        } else if (!validateEmail(emailInput.value)) {
            showError('email', 'emailError', 'E-mail inválido');
            isValid = false;
        }

        // Validar senha
        if (!passwordInput.value) {
            showError('password', 'passwordError', 'Senha é obrigatória');
            isValid = false;
        }

        return isValid;
    }

    // Submissão do formulário: valida e deixa o POST seguir para o PHP
    loginForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Remover erros ao digitar
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            clearErrors();
        }
    });

    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            clearErrors();
        }
    });

    console.log('Login page ready (POST to PHP)');
});
