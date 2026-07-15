-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/06/2026 às 00:51
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `infinity_tech`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `documento` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `diagnostico` text NOT NULL,
  `taxa_deslocacao` decimal(10,2) DEFAULT 0.00,
  `taxa_servico` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') DEFAULT 'Pendente',
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `orcamentos`
--

INSERT INTO `orcamentos` (`id`, `id_cliente`, `diagnostico`, `taxa_deslocacao`, `taxa_servico`, `valor_total`, `status`, `data_criacao`) VALUES
(1, 5, 'Ar-condicionado pingando água', 50.00, 150.00, 200.00, 'Pendente', '2026-06-29 19:05:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamento_itens`
--

CREATE TABLE `orcamento_itens` (
  `id` int(11) NOT NULL,
  `id_orcamento` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `idProdutos` int(11) NOT NULL,
  `Nome_produto` varchar(255) NOT NULL,
  `Modelo` varchar(255) DEFAULT NULL,
  `Marca` varchar(255) DEFAULT NULL,
  `Preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`idProdutos`, `Nome_produto`, `Modelo`, `Marca`, `Preco`) VALUES
(1, 'Gás Refrigerante R410a', 'Cilindro 11.3kg', 'Dugold', 450.00),
(2, 'Tubo de Cobre 1/4', 'Rolo 15m', 'Eluma', 120.00),
(3, 'Capacitor Duplo 35+5uF', '380V', 'EOS', 45.00),
(4, 'Placa Universal', 'Split On/Off', 'Suryha', 180.00),
(5, 'Motor Ventilador', 'Evaporadora 9000 BTUs', 'Gree', 250.00),
(6, 'Isolamento Térmico 1/4', 'Barra 2m', 'Armacell', 15.00),
(7, 'Fita PVC', 'Sem adesivo 10m', 'Tigre', 8.50);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` varchar(20) DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `tipo`) VALUES
(1, 'João Pedro José Boaventura', 'João_boaventura', '$2y$10$eihsU8kYGNHRxGNwTBUXoeV66ralhqT4UXFB.Uvh5hJA8l6eYXSW.', 'cliente'),
(2, 'Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(3, 'Usuário Teste', 'teste', '123456', 'cliente'),
(4, 'Geovanna', 'geodiva15', '$2y$10$pXFUadZpCw2acogh2UQsoeX8j7pNr2RXkGx7t7xeQMMIr8ejz6V3C', 'cliente'),
(5, 'João', 'joao-silva', '$2y$10$aGucPdBEIZYV5eE6xRl2ZOO4nAOgu.ABKNp4oB0AP2QKnCrK2cWlK', 'cliente'),
(6, 'Julia Weiss', 'Ju', '$2y$10$eK3Pic2qIIAGsfn1RVQzpuscQzV5vT1qHkFAZQhxdOE/vvwXInMRe', 'funcionario'),
(7, 'Julia Weiss 2', 'Ju2', '$2y$10$fcNBlXRQIO/SjCO8mxSVieCrMmqZu21AFmJohnl4p/N.uctPorO8u', 'cliente'),
(8, 'geo', 'geo', '$2y$10$3TRRScS1qugKwgrzFsLa9enSsFUqG0f/zuVY48dGjSoCGu6mHF6s2', 'cliente'),
(9, 'Julia Weiss', 'Weiss', '$2y$10$p4tI8CfQpAdHGA2NJdw1GOJ7K6VnCds.KExkgN7y1ryTFWvVStXaK', 'funcionario');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orcamento` (`id_orcamento`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`idProdutos`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `idProdutos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `orcamentos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD CONSTRAINT `orcamento_itens_ibfk_1` FOREIGN KEY (`id_orcamento`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orcamento_itens_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`idProdutos`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
