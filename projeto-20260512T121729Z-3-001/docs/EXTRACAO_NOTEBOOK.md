# 📔 Extração Detalhada - NotebookLM (FunShirt)

Esta é a extração completa e detalhada dos 9 tópicos do projeto FunShirt, obtida via NotebookLM.

### 1. OBJETIVO
O projeto consiste no desenvolvimento de uma aplicação Web baseada no servidor para a loja online **"FunShirt"**, especializada na venda de t-shirts estampadas. A aplicação deve ser obrigatoriamente desenvolvida utilizando a **Framework Laravel**.

### 2. CENÁRIO
A "FunShirt" pretende expandir a sua presença física para o mercado digital. O sistema deve gerir um catálogo de t-shirts (com variações de tamanho, cor e estampados) e oferecer uma experiência de compra intuitiva:
*   **Carrinho de Compras:** Deve ser mantido na sessão do servidor. Permite definir a imagem (catálogo ou personalizada), cor, tamanho e quantidade.
*   **Checkout:** Exige autenticação. O cliente valida dados de faturação, método de pagamento e endereço de envio (pré-preenchidos a partir do perfil).
*   **Área de Cliente:** Histórico de encomendas e gestão de perfil.
*   **Administração:** Painel para gestão de encomendas, clientes e stock.

### 3. PROJETO E RESTRIÇÕES
*   **Tecnologia:** Laravel 12+ e base de dados SQLite.
*   **Arquitetura:** Estritamente *Server-Side*.
*   **JavaScript:** Proibido para lógica de aplicação (permitido apenas para efeitos visuais residuais).
*   **Livewire:** Autorizado para interatividade, pois mantém a lógica no PHP/Servidor.
*   **Autenticação:** Deve ser **implementada manualmente**. É proibido o uso de pacotes prontos como Jetstream ou Breeze, assim como geradores automáticos como Filament ou Laravel Nova.

### 4. GRUPOS DE FUNCIONALIDADES
O projeto divide-se em 10 grupos, com destaque para:
*   **G1 (Autenticação):** Registo, login, recuperação de password e bloqueio de contas.
*   **G2 (Catálogo):** CRUD de imagens e categorias, pesquisa e upload de imagens personalizadas.
*   **G3 (Cliente):** Gestão dinâmica do carrinho e histórico de encomendas com PDF.
*   **G4 (Admin):** Gestão do fluxo de estados das encomendas e faturação automática.
*   **G5-G10:** Dashboards, notificações por email, configuração de preços/IVA, gestão de inventário e cupões de desconto.

### 5. BASE DE DADOS
Estrutura relacional composta por:
*   `users`: Dados de perfil, NIF e tipo de acesso (Cliente/Admin).
*   `categories` e `images`: Organização das estampas.
*   `tshirts`: Modelos base (cor/tamanho).
*   `prices`: Configuração de preços e descontos de quantidade.
*   `orders` e `order_items`: Registo histórico das transações e detalhes dos produtos.

### 6. STORAGE
Utilização do sistema de **Storage do Laravel**:
*   **Público (`public/`):** Fotos de perfil, imagens do catálogo e assets do site.
*   **Privado:** Imagens personalizadas enviadas por clientes (acesso restrito ao dono e admins).
*   **Documentos:** PDFs de faturas/recibos gerados pelo sistema.
*   **Regra de Ouro:** Ao apagar um registo na BD, o ficheiro correspondente no Storage deve ser removido.

### 7. PLATAFORMA DE PAGAMENTOS
O sistema utiliza apenas **simulação de pagamentos**. No checkout, o utilizador escolhe entre **Visa, PayPal ou MB WAY**. A aplicação "valida" a transação internamente, altera o estado da encomenda para "Paga" e gera o recibo, sem necessidade de integração com gateways reais.

### 8. ENTREGA
*   **Formato:** Ficheiro .zip ou .7z via Moodle.
*   **Conteúdo:** Código-fonte completo, base de dados `database.sqlite` (com seeds de teste) e um **relatório em PDF** descrevendo as decisões técnicas e limitações.

### 9. AVALIAÇÃO
A nota final baseia-se nos seguintes pesos:
*   **20%** - Autenticação e Perfil (G1).
*   **20%** - Catálogo e Imagens (G2).
*   **25%** - Carrinho e Encomendas - Cliente (G3).
*   **25%** - Gestão de Encomendas - Admin (G4).
*   **10%** - Dashboards e funcionalidades extra (G5-G10).
*   **Fatores Transversais:** Qualidade e modularidade do código (Blade components, partials), segurança (autorização robusta) e eficiência (otimização de queries, cache e uso de Queues).
