-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 03, 2015 at 12:16 AM
-- Server version: 10.0.21-MariaDB
-- PHP Version: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `safebook`
--
CREATE DATABASE IF NOT EXISTS `safebook` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `safebook`;

-- --------------------------------------------------------

--
-- Table structure for table `emissao`
--

CREATE TABLE `emissao` (
  `idEmissao` int(11) NOT NULL,
  `idMensagem` int(11) NOT NULL,
  `dataEmissao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mensagem`
--

CREATE TABLE `mensagem` (
  `idMensagem` int(11) NOT NULL,
  `texto` varchar(300) NOT NULL,
  `encriptado` tinyint(1) NOT NULL,
  `chave` varchar(300) NOT NULL,
  `dataEmissao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idEmissor` int(11) NOT NULL,
  `idReceptor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `idUtilizador` int(11) NOT NULL,
  `nomeUtilizador` varchar(30) NOT NULL,
  `salt` char(32) NOT NULL,
  `password` char(64) NOT NULL COMMENT 'guarda hash da password',
  `idCertificado` varchar(20) NOT NULL COMMENT '"unsigned" para guardar de 0 para cima ',
  `ultimaMensagem` int(10) UNSIGNED NOT NULL COMMENT '"unsigned" para guardar de 0 para cima '
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `utilizador_emissao`
--

CREATE TABLE `utilizador_emissao` (
  `idEmissor` int(11) NOT NULL,
  `idReceptor` int(11) NOT NULL,
  `idEmissao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `emissao`
--
ALTER TABLE `emissao`
  ADD PRIMARY KEY (`idEmissao`),
  ADD UNIQUE KEY `idMensagem` (`idMensagem`);

--
-- Indexes for table `mensagem`
--
ALTER TABLE `mensagem`
  ADD PRIMARY KEY (`idMensagem`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`idUtilizador`);

--
-- Indexes for table `utilizador_emissao`
--
ALTER TABLE `utilizador_emissao`
  ADD PRIMARY KEY (`idEmissor`,`idReceptor`,`idEmissao`),
  ADD KEY `idReceptor` (`idReceptor`),
  ADD KEY `idEmissao` (`idEmissao`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `emissao`
--
ALTER TABLE `emissao`
  MODIFY `idEmissao` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mensagem`
--
ALTER TABLE `mensagem`
  MODIFY `idMensagem` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `idUtilizador` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `emissao`
--
ALTER TABLE `emissao`
  ADD CONSTRAINT `emissao_ibfk_1` FOREIGN KEY (`idMensagem`) REFERENCES `mensagem` (`idMensagem`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `utilizador_emissao`
--
ALTER TABLE `utilizador_emissao`
  ADD CONSTRAINT `utilizador_emissao_ibfk_1` FOREIGN KEY (`idEmissor`) REFERENCES `utilizador` (`idUtilizador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `utilizador_emissao_ibfk_2` FOREIGN KEY (`idReceptor`) REFERENCES `utilizador` (`idUtilizador`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `utilizador_emissao_ibfk_3` FOREIGN KEY (`idEmissao`) REFERENCES `emissao` (`idEmissao`) ON DELETE CASCADE ON UPDATE CASCADE;
