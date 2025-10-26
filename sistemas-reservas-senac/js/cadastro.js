// Cadastro Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const cadastroForm = document.getElementById('cadastroForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const telefoneInput = document.getElementById('telefone');

    // Toggle mostrar/ocultar senha
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });

    // Máscara de telefone (opcional)
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        }
    });

    // Validar força da senha
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        return strength;
    }

    // Atualizar indicador de força da senha
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);

        strengthBar.className = 'strength-bar-fill';
        
        if (password.length === 0) {
            strengthBar.style.width = '0';
            strengthText.textContent = 'Use pelo menos 8 caracteres';
            return;
        }

        if (strength <= 2) {
            strengthBar.classList.add('weak');
            strengthText.textContent = 'Senha fraca';
        } else if (strength <= 3) {
            strengthBar.classList.add('medium');
            strengthText.textContent = 'Senha média';
        } else {
            strengthBar.classList.add('strong');
            strengthText.textContent = 'Senha forte';
        }
    });

    // Validação de e-mail
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Limpar erro específico
    function clearError(inputId) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(inputId + 'Error');
        if (input) input.classList.remove('error');
        if (error) error.textContent = '';
    }

    // Mostrar erro
    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(inputId + 'Error');
        if (input) input.classList.add('error');
        if (error) error.textContent = message;
    }

    // Validação do formulário
    function validateForm() {
        let isValid = true;

        // Limpar todos os erros
        ['nome', 'sobrenome', 'email', 'password', 'confirmPassword'].forEach(clearError);

        // Validar nome
        if (!document.getElementById('nome').value.trim()) {
            showError('nome', 'Nome é obrigatório');
            isValid = false;
        }

        // Validar sobrenome
        if (!document.getElementById('sobrenome').value.trim()) {
            showError('sobrenome', 'Sobrenome é obrigatório');
            isValid = false;
        }

        // Validar e-mail
        const email = document.getElementById('email').value;
        if (!email.trim()) {
            showError('email', 'E-mail é obrigatório');
            isValid = false;
        } else if (!validateEmail(email)) {
            showError('email', 'E-mail inválido');
            isValid = false;
        }

        // Validar senha
        const password = passwordInput.value;
        if (!password) {
            showError('password', 'Senha é obrigatória');
            isValid = false;
        } else if (password.length < 8) {
            showError('password', 'A senha deve ter pelo menos 8 caracteres');
            isValid = false;
        }

        // Validar confirmação de senha
        const confirmPassword = confirmPasswordInput.value;
        if (!confirmPassword) {
            showError('confirmPassword', 'Confirme sua senha');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError('confirmPassword', 'As senhas não coincidem');
            isValid = false;
        }

        // Validar termos
        if (!document.getElementById('terms').checked) {
            alert('Você deve aceitar os Termos de Uso e Política de Privacidade');
            isValid = false;
        }

        return isValid;
    }

    // Submissão do formulário: valida e deixa o POST seguir para o PHP
    cadastroForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Remover erros ao digitar
    ['nome', 'sobrenome', 'email', 'password', 'confirmPassword'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    clearError(fieldId);
                }
            });
        }
    });

    console.log('Cadastro page ready (POST to PHP)');
});
