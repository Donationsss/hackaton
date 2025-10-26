# Sistema de Reservas SENAC

Sistema digital centralizado para gerenciamento de reservas de espaços e eventos do SENAC.

## 📋 Sobre o Projeto

Este sistema foi desenvolvido para substituir o processo manual de reservas por planilhas, oferecendo:
- Agenda interativa de eventos
- Sistema de aprovação de reservas
- Múltiplos perfis de acesso (Admin, Colaborador, Visualização)
- Dashboard com estatísticas em tempo real
- Relatórios gerenciais
- Interface moderna e responsiva

## 🚀 Estrutura do Projeto

```
sistemas-reservas-senac/
│
├── index.html              # Redirecionamento para landing page
├── landing.html            # Página inicial/apresentação
├── login.html              # Página de autenticação
├── cadastro.html           # Cadastro de novos usuários
├── recuperar-senha.html    # Recuperação de senha
├── dashboard.html          # Dashboard principal do sistema
│
├── pages/                  # Páginas internas
│   ├── reservas.html       # Gerenciamento de reservas
│   ├── espacos.html        # Gerenciamento de espaços
│   ├── relatorios.html     # Relatórios e estatísticas
│   ├── colaboradores.html  # Gerenciamento de usuários
│   └── configuracoes.html  # Configurações do sistema
│
├── css/                    # Arquivos de estilo
│   ├── comum.css           # Estilos compartilhados (variáveis, reset, componentes)
│   ├── landing.css         # Estilos da landing page
│   ├── auth.css            # Estilos de autenticação (login, cadastro)
│   ├── dashboard.css       # Estilos do dashboard
│   └── paginas.css         # Estilos das páginas internas
│
└── js/                     # Arquivos JavaScript
    ├── landing.js          # Funcionalidades da landing page
    ├── login.js            # Lógica de autenticação
    ├── cadastro.js         # Validação e cadastro
    ├── recuperar-senha.js  # Recuperação de senha
    ├── dashboard.js        # Funcionalidades do dashboard
    └── paginas.js          # Funcionalidades das páginas internas
```

## 🎨 Identidade Visual

O sistema segue as cores oficiais do SENAC:
- **Azul Principal**: #004A8D
- **Azul Escuro**: #003366
- **Laranja**: #F7941D

## 📱 Páginas

### Públicas
- **Landing Page** (`landing.html`): Apresentação do sistema com funcionalidades e benefícios
- **Login** (`login.html`): Autenticação de usuários
- **Cadastro** (`cadastro.html`): Registro de novos usuários
- **Recuperar Senha** (`recuperar-senha.html`): Recuperação de acesso

### Internas (Requer Login)
- **Dashboard** (`dashboard.html`): Visão geral com estatísticas e ações rápidas
- **Reservas** (`pages/reservas.html`): Gerenciamento completo de reservas
- **Espaços** (`pages/espacos.html`): Cadastro e gerenciamento de espaços
- **Relatórios** (`pages/relatorios.html`): Relatórios e análises
- **Colaboradores** (`pages/colaboradores.html`): Gerenciamento de usuários
- **Configurações** (`pages/configuracoes.html`): Configurações do sistema

## 🔧 Funcionalidades Implementadas

### Landing Page
- Animações suaves ao scroll
- Contador animado de estatísticas
- Layout responsivo
- Chamadas para ação (CTAs)

### Autenticação
- Validação de formulários em tempo real
- Indicador de força de senha
- Toggle para mostrar/ocultar senha
- Máscaras de entrada (telefone, etc.)
- Feedback visual de erros

### Dashboard
- Calendário interativo gerado dinamicamente
- Estatísticas em tempo real
- Cards de ações rápidas
- Reservas recentes
- Sistema de notificações

### Páginas Internas
- Tabelas com dados de exemplo
- Filtros funcionais
- Botões de ação com confirmação
- Sistema de badges de status
- Paginação

## 💻 Como Usar

1. **Acessar o sistema**: Abra o arquivo `index.html` no navegador
   - Você será automaticamente redirecionado para `landing.html`

2. **Fazer Login**: 
   - Clique em "Fazer Login" na landing page
   - Use qualquer e-mail e senha válidos (modo demo)
   - Marque "Lembrar de mim" para manter a sessão

3. **Navegar pelo Sistema**:
   - Use o menu de navegação no topo
   - Explore as diferentes seções
   - Clique nos botões para ver as funcionalidades

## 🎯 Perfis de Acesso

### Gestor/Admin
- Acesso completo ao sistema
- Aprovar/rejeitar reservas
- Gerenciar espaços e usuários
- Visualizar relatórios completos

### Colaborador
- Criar solicitações de reserva
- Visualizar próprias reservas
- Acesso limitado a relatórios

### Visualização
- Apenas visualizar agenda e reservas confirmadas
- Sem permissão de criação ou edição

## 🔒 Segurança

- Validação de dados no frontend
- Proteção de rotas (redirecionamento se não autenticado)
- Senhas nunca exibidas em texto plano
- Indicador de força de senha no cadastro

## 📊 Tecnologias Utilizadas

- HTML5
- CSS3 (com variáveis CSS)
- JavaScript (ES6+)
- LocalStorage para persistência de dados
- Design Responsivo

## 🎨 Padrões de Código

### CSS
- Variáveis CSS para cores e espaçamentos
- Metodologia BEM (parcial)
- Mobile-first approach
- Comentários descritivos

### JavaScript
- Event listeners organizados
- Funções reutilizáveis
- Validações consistentes
- Console.log para debug

## 🚧 Próximos Passos

Para produção, será necessário:
- Integração com backend/API
- Banco de dados real
- Sistema de autenticação JWT
- Envio de e-mails
- Upload de arquivos
- Notificações em tempo real
- Testes automatizados
- Deploy em servidor

## 📝 Notas de Desenvolvimento

- Este é um protótipo funcional para demonstração
- Os dados são simulados e armazenados no LocalStorage
- A autenticação é simulada (qualquer credencial válida funciona)
- Ideal para apresentação e testes de UX/UI

## 👥 Equipe

Desenvolvido para o Hackathon SENAC 2025

## 📄 Licença

Sistema proprietário © 2025 SENAC
