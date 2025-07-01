-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: projeto_tcc
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avaliacao_projeto`
--

DROP TABLE IF EXISTS `avaliacao_projeto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacao_projeto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int DEFAULT NULL,
  `avaliador_id` int DEFAULT NULL,
  `avaliacao` text,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  KEY `avaliador_id` (`avaliador_id`),
  CONSTRAINT `avaliacao_projeto_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`),
  CONSTRAINT `avaliacao_projeto_ibfk_2` FOREIGN KEY (`avaliador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacao_projeto`
--

LOCK TABLES `avaliacao_projeto` WRITE;
/*!40000 ALTER TABLE `avaliacao_projeto` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliacao_projeto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avaliacoes`
--

DROP TABLE IF EXISTS `avaliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int NOT NULL,
  `avaliador_id` int NOT NULL,
  `criatividade` int DEFAULT '0',
  `metodo_cientifico` int DEFAULT '0',
  `profundidade` int DEFAULT '0',
  `aplicabilidade` int DEFAULT '0',
  `apresentacao` int DEFAULT '0',
  `carater_integrador` int DEFAULT '0',
  `observacoes` text,
  `data_avaliacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT NULL,
  `comentario` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_avaliador_projeto` (`projeto_id`,`avaliador_id`),
  UNIQUE KEY `projeto_avaliador_unique` (`projeto_id`,`avaliador_id`),
  KEY `fk_avaliacao_avaliador` (`avaliador_id`),
  CONSTRAINT `fk_avaliacao_avaliador` FOREIGN KEY (`avaliador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_avaliacao_projeto` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacoes`
--

LOCK TABLES `avaliacoes` WRITE;
/*!40000 ALTER TABLE `avaliacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avaliador_areas`
--

DROP TABLE IF EXISTS `avaliador_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliador_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `avaliador_id` int NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `quantidade` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avaliador_id` (`avaliador_id`),
  CONSTRAINT `avaliador_areas_ibfk_1` FOREIGN KEY (`avaliador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliador_areas`
--

LOCK TABLES `avaliador_areas` WRITE;
/*!40000 ALTER TABLE `avaliador_areas` DISABLE KEYS */;
INSERT INTO `avaliador_areas` VALUES (1,30,'Ciências Agrárias',1),(2,30,'Engenharias',1),(3,31,'Ciências Biológicas',1),(4,33,'Ciências Exatas e da Terra',1),(5,34,'Ciências Exatas e da Terra',1),(6,35,'Ciências Exatas e da Terra',1),(7,36,'Ciências Exatas e da Terra',1),(8,37,'Engenharias',1);
/*!40000 ALTER TABLE `avaliador_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracoes_submissao`
--

DROP TABLE IF EXISTS `configuracoes_submissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes_submissao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracoes_submissao`
--

LOCK TABLES `configuracoes_submissao` WRITE;
/*!40000 ALTER TABLE `configuracoes_submissao` DISABLE KEYS */;
INSERT INTO `configuracoes_submissao` VALUES (1,'2025-05-23','2025-06-26');
/*!40000 ALTER TABLE `configuracoes_submissao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudantes`
--

DROP TABLE IF EXISTS `estudantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estudantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `projeto_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  CONSTRAINT `estudantes_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudantes`
--

LOCK TABLES `estudantes` WRITE;
/*!40000 ALTER TABLE `estudantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int DEFAULT NULL,
  `feedback` text,
  `usuario_id` int DEFAULT NULL,
  `tipo_feedback` enum('orientador','avaliador','coordenador','professor','estudante') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `login_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orientadores`
--

DROP TABLE IF EXISTS `orientadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orientadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_orientador` varchar(255) DEFAULT NULL,
  `cpf_orientador` varchar(14) DEFAULT NULL,
  `email_orientador` varchar(255) DEFAULT NULL,
  `instituicao_orientador` varchar(255) DEFAULT NULL,
  `co_nome` varchar(255) DEFAULT NULL,
  `co_cpf` varchar(20) DEFAULT NULL,
  `co_email` varchar(255) DEFAULT NULL,
  `id_projeto` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orientadores`
--

LOCK TABLES `orientadores` WRITE;
/*!40000 ALTER TABLE `orientadores` DISABLE KEYS */;
INSERT INTO `orientadores` VALUES (12,'Robson','424242424242','robson124@gmail.com','IFSP','','','',NULL),(13,'Robson','444.444.444-44','robson@gmail.com','IFSP','','','',NULL),(14,'Robson','666.666.666-66','robson@gmail.com','IFSP','','','',NULL),(15,'Robson','412.414.161-61','2@gmail.com','IFSP','','','',NULL),(16,'Robson','151.511.616-66','robson@gmail.com','IFSP','','','',NULL),(17,'Robson','451.515.151-51','robson@gmail.com','IFSP','','','',NULL),(18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,25),(20,'gbwuegiw','111.222.444-65','gb@gmail.com','IFSp','','','',26),(21,'kleber',NULL,NULL,NULL,NULL,NULL,NULL,15),(22,'Kleber Jose','777.777.777-77','kleberj@gmail.com','IFSP','','','',28),(23,'Juliana ','566.222.226-66','juliana@gmail.com','USP','','','',29),(24,'Gabriel Silva','777.777.777-77','gabriels@gmail.com','IFSP','','','',30),(25,'hdfhdhdb4','355.151.222-22','fbsbsew@gmail.com','IFSP','','','',31),(26,'Robson Lopes','515.153.453-53','robsonlopesss@gmail.com','IFSP','','','',33),(27,'Robson','777.777.777-77','robson@gmail.com','IFSP','','','',34),(28,'Robson','566.222.226-66','robson@gmail.com','IFSP','','','',35),(30,'Juliana ','555.555.555-55','dsada@gmail.com','IFSP','','','',37);
/*!40000 ALTER TABLE `orientadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantes`
--

DROP TABLE IF EXISTS `participantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(100) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `tipo_participante` varchar(50) DEFAULT NULL,
  `escola` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `serie_ano` varchar(20) DEFAULT NULL,
  `nivel_ensino` varchar(50) DEFAULT NULL,
  `area_projeto` varchar(100) DEFAULT NULL,
  `termo_compromisso` tinyint(1) DEFAULT '0',
  `tipo_curso` varchar(50) DEFAULT NULL,
  `serie` varchar(20) DEFAULT NULL,
  `instituicao` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `dependencia` varchar(50) DEFAULT NULL,
  `termo` tinyint(1) DEFAULT '0',
  `id_projeto` int DEFAULT NULL,
  `nome_social` varchar(255) DEFAULT NULL,
  `etnia` varchar(255) DEFAULT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `identidade_genero` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantes`
--

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;
INSERT INTO `participantes` VALUES (19,'ggggggg','0011-11-11','gggggggggg','111111111','gggggg@gmail.com',NULL,NULL,NULL,'2',NULL,NULL,NULL,NULL,0,'Ensino Médio','2','2','2','2',1,NULL,NULL,NULL,NULL,NULL),(20,'22222222','2020-05-04','2222@gmail.com',NULL,'2222@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(21,'dwqdqwdwqdqd','2222-02-22','qwdqdqdqdq','wdqdqdqdq','dqdqdqdq@gmail.com',NULL,NULL,NULL,'5',NULL,NULL,NULL,NULL,0,'Ensino Médio','4','5','5','5',1,NULL,NULL,NULL,NULL,NULL),(28,'Gabriel','2001-08-04','277.777.777-77','11995187364','gabriel1234@gmail.com',NULL,NULL,NULL,'SC',NULL,NULL,NULL,NULL,0,'Ensino Técnico Integrado','4','IFSP','Guarulhos','Municipal',1,NULL,NULL,NULL,NULL,NULL),(30,'Gabriel','2003-02-22','313.131.313-13','1198888888','pedro@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','5','IFSP','SANTA ISABEL','Municipal',1,NULL,NULL,NULL,NULL,NULL),(34,'jfabfkasfjabfafa','2001-02-22','515.151.515-15','11995187364','2@GMAIL.COM',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','5','IFSP','Guarulhos','Municipal',1,NULL,NULL,NULL,NULL,NULL),(36,'fafafafaf','2006-02-22','252.525.252-52','11995187364','2@GMAIL.COM',NULL,NULL,NULL,'SC',NULL,NULL,NULL,NULL,0,'Ensino Médio','3 ano','Ifsp','GuarulhoS','MunicipaL',1,NULL,NULL,NULL,NULL,NULL),(37,'Paulo','2000-02-03','786.786.797-96',NULL,'paulo@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL),(38,'gbauigabouigbauobaoga','2000-11-04','222.222.222-22','1199999999','pedro@gmail.com',NULL,NULL,NULL,'RJ',NULL,NULL,NULL,NULL,0,'Ensino Médio','5','IFSp','Guarulhos','Municipal',1,25,NULL,NULL,NULL,NULL),(39,'Carlos a','2000-07-21','444.444.444-44',NULL,'carlos@gmail.comm',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,25,NULL,NULL,NULL,NULL),(40,'ugebqwgbewigbiwgw','2000-03-22','555.555.555-55','11945195195','555@gmail.com',NULL,NULL,NULL,'PR',NULL,NULL,NULL,NULL,0,'Ensino Médio','6','IFSP','Guarulhos','Municipal',1,26,NULL,NULL,NULL,NULL),(41,'Miguel','2001-05-22','999.999.999-99',NULL,'99@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,26,NULL,NULL,NULL,NULL),(42,'Pedro Lopes','1999-02-08','222.222.222-22','11912345675','pedrol@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','3 ano','IFSP','Pirapora','Municipal',1,27,NULL,NULL,NULL,NULL),(43,'Pedro Lopes','1999-02-08','222.222.222-22','11912345675','pedrol@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','3 ano','IFSP','Pirapora','Municipal',1,28,NULL,NULL,NULL,NULL),(44,'Jose Felipe','1998-05-31','555.555.555-55',NULL,'josef@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,28,NULL,NULL,NULL,NULL),(45,'Josiel da Silva','2006-03-04','414.141.444-44','1199237642','josiel@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','3 ano','USP','São Paulo','Administrativa',1,29,NULL,NULL,NULL,NULL),(46,'Nicolas','2005-06-07','757.575.555-55',NULL,'nicolas@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,29,NULL,NULL,NULL,NULL),(47,'Nicolas','2000-10-04','444.444.444-44','11995184141','nicolas@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','Guarulhos','Municipal',1,30,NULL,NULL,NULL,NULL),(48,'Diego','2001-02-03','555.555.555-55',NULL,'diego@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,30,NULL,NULL,NULL,NULL),(49,'kfanfanfa','2000-03-20','515.111.111-11','1141414145','dada@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Técnico Integrado','7 ano','IFSP','Guarulhos','Municipal',1,31,NULL,NULL,NULL,NULL),(50,'gagagaga','2004-02-22','345.141.111-11',NULL,'dfafafa@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,31,NULL,NULL,NULL,NULL),(51,'Marcio das Neves','2003-10-20','412.414.141-41','11995187364','marciodasneves@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','Guarulhos','Municipal',1,32,NULL,NULL,NULL,NULL),(52,'Marcio das Neves','2003-10-20','412.414.141-41','11995187364','marciodasneves@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','Guarulhos','Municipal',1,33,NULL,NULL,NULL,NULL),(53,'Gabriel das Neves','2000-02-22','451.515.151-51',NULL,'gabrieldasneves@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,33,NULL,NULL,NULL,NULL),(54,'Pedro Lopes','2001-02-20','414.141.444-44','11995187364','pedro@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','Guarulhos','Federal',1,34,NULL,NULL,NULL,NULL),(55,'Carlos','2003-02-12','757.575.555-55',NULL,'2@gamildamd.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,34,NULL,NULL,NULL,NULL),(56,'Pedro Lopes','1978-10-20','414.141.444-44','11995187364','pedro@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','São Paulo','Federal',1,35,NULL,NULL,NULL,NULL),(57,'Carlos','1978-10-20','757.575.555-55',NULL,'carlos@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,35,NULL,NULL,NULL,NULL),(60,'Henrique','2003-10-20','444.444.444-44','11995187364','44henr@gmail.com',NULL,NULL,NULL,'SP',NULL,NULL,NULL,NULL,0,'Ensino Médio','7 ano','IFSP','Guarulhos','Federal',1,37,NULL,NULL,NULL,NULL),(61,'Nicolas','2004-02-22','444.444.444-44',NULL,'nid@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,0,37,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projeto_avaliador`
--

DROP TABLE IF EXISTS `projeto_avaliador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projeto_avaliador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int DEFAULT NULL,
  `avaliador_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  KEY `avaliador_id` (`avaliador_id`),
  CONSTRAINT `projeto_avaliador_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projeto_avaliador_ibfk_2` FOREIGN KEY (`avaliador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projeto_avaliador`
--

LOCK TABLES `projeto_avaliador` WRITE;
/*!40000 ALTER TABLE `projeto_avaliador` DISABLE KEYS */;
/*!40000 ALTER TABLE `projeto_avaliador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projeto_likes`
--

DROP TABLE IF EXISTS `projeto_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projeto_likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projeto_usuario` (`projeto_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `projeto_likes_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`),
  CONSTRAINT `projeto_likes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projeto_likes`
--

LOCK TABLES `projeto_likes` WRITE;
/*!40000 ALTER TABLE `projeto_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `projeto_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projetos`
--

DROP TABLE IF EXISTS `projetos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projetos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descricao` text,
  `usuario_id` int DEFAULT NULL,
  `participacao` enum('orientador','coorientador','estudante') NOT NULL,
  `numero_estudantes` int DEFAULT '0',
  `estado_instituicao` varchar(100) DEFAULT NULL,
  `cidade_instituicao` varchar(100) DEFAULT NULL,
  `nome_instituicao` varchar(100) DEFAULT NULL,
  `orientador` varchar(255) DEFAULT NULL,
  `coorientador` varchar(255) DEFAULT NULL,
  `estudantes` varchar(255) DEFAULT NULL,
  `categoria_projeto` varchar(255) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_termino` date DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `data_submissao` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `projeto_integrador` varchar(255) DEFAULT NULL,
  `resumo_arquivo` varchar(255) DEFAULT NULL,
  `link_poster` varchar(255) DEFAULT NULL,
  `link_video` varchar(255) DEFAULT NULL,
  `status` enum('aprovado','reprovado','pendente','alteracoes') DEFAULT NULL,
  `imagem_projeto` varchar(255) DEFAULT NULL,
  `comentario_alteracoes` text,
  `avaliador_id` int DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `nome_social` varchar(255) DEFAULT NULL,
  `resumo_texto` text,
  `palavras_chave` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projetos`
--

LOCK TABLES `projetos` WRITE;
/*!40000 ALTER TABLE `projetos` DISABLE KEYS */;
/*!40000 ALTER TABLE `projetos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projetos_avaliados`
--

DROP TABLE IF EXISTS `projetos_avaliados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projetos_avaliados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int DEFAULT NULL,
  `avaliacao` text,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  CONSTRAINT `projetos_avaliados_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projetos_avaliados`
--

LOCK TABLES `projetos_avaliados` WRITE;
/*!40000 ALTER TABLE `projetos_avaliados` DISABLE KEYS */;
/*!40000 ALTER TABLE `projetos_avaliados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projetos_submetidos`
--

DROP TABLE IF EXISTS `projetos_submetidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projetos_submetidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descricao` text,
  `participantes` text,
  `status` enum('submetido','em andamento','finalizado') DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_termino` date DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `projetos_submetidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projetos_submetidos`
--

LOCK TABLES `projetos_submetidos` WRITE;
/*!40000 ALTER TABLE `projetos_submetidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `projetos_submetidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `situacao_projeto`
--

DROP TABLE IF EXISTS `situacao_projeto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `situacao_projeto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projeto_id` int DEFAULT NULL,
  `situacao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projeto_id` (`projeto_id`),
  CONSTRAINT `situacao_projeto_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos_submetidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `situacao_projeto`
--

LOCK TABLES `situacao_projeto` WRITE;
/*!40000 ALTER TABLE `situacao_projeto` DISABLE KEYS */;
/*!40000 ALTER TABLE `situacao_projeto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submeter_projeto`
--

DROP TABLE IF EXISTS `submeter_projeto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submeter_projeto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) DEFAULT NULL,
  `descricao` text,
  `participantes` text,
  `status` enum('submetido','em andamento','finalizado') DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_termino` date DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `submeter_projeto_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submeter_projeto`
--

LOCK TABLES `submeter_projeto` WRITE;
/*!40000 ALTER TABLE `submeter_projeto` DISABLE KEYS */;
/*!40000 ALTER TABLE `submeter_projeto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens_acesso`
--

DROP TABLE IF EXISTS `tokens_acesso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tokens_acesso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(128) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `criado_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens_acesso`
--

LOCK TABLES `tokens_acesso` WRITE;
/*!40000 ALTER TABLE `tokens_acesso` DISABLE KEYS */;
INSERT INTO `tokens_acesso` VALUES (1,'07bc1b2bc454312f5396e05d811df9b2511c087258fad6b84f1f555a3d8f085d','avaliador','2025-05-25 22:59:32'),(2,'9764bac0de45af43863b50dc063b809deb3b8412b01313d8ad042f8196a7e8d4','avaliador','2025-05-25 22:59:38'),(3,'141ab8a7b7de94b0dea71f0aacd22dbeed0b8a9f627e9ff4c3be7c379ca9aa86','avaliador','2025-05-25 22:59:38'),(4,'e5a40ae843a4207074169928a0276512bd38204b0a5076be0b576992e2686b7c','avaliador','2025-05-25 22:59:41'),(5,'4975c1e27eb33b2a9a1c83bc0331314c788953898d2f2f095c1553512d966e1e','avaliador','2025-05-25 22:59:42'),(6,'29cfad24de1e9d707ae5154de9474091fba45cfdb6a2d034b0e6c85995b87fb8','avaliador','2025-05-25 22:59:43'),(7,'3597d678daf317dbf8c6f317a94441a3bb077e773e3fc5d754d10e606e5b2353','avaliador','2025-05-25 22:59:46'),(8,'740dc86e2fb1f04b1d4b6881c829578b78f3f23970db2c1a6b07b6f9a6d09ce6','avaliador','2025-05-25 22:59:47'),(9,'30947a840918985eaff238233d82393f40101443c3e790107821a866b57a3cb6','avaliador','2025-05-25 22:59:48'),(10,'b5164eaf39d0309a05a20027366644fa7956b6d420f365bea452a520b731c1a2','avaliador','2025-05-25 23:00:13'),(11,'58b1c0e0506bde90d93ac6cc113dceb4e777019c542b6687197df08a99c82f00','avaliador','2025-05-25 23:00:13'),(12,'601df39c0955c3b72e57a44f6e99cdb9ec0ddb7308c625337e94a0755b8ebdfe','avaliador','2025-05-25 23:00:14'),(13,'4bfe55f5c2acdfdab4f92b1c580e59baaf0413f6489a372502dd457686152f9b','avaliador','2025-05-25 23:00:35'),(14,'a0eeea9b0bd195ac7019f1e0ef14540a6898b7c1fba5f7862234f418b90ade30','avaliador','2025-05-25 23:01:05'),(15,'65c71b0de771fbf607b079ca29fd14bfdb0275aa2f4e9ecf557b8b1808456864','avaliador','2025-05-25 23:03:04'),(16,'df48036d8eb76159b7d8c1a5c7ef583370e9adeecbe5827e5b9bcd4a93b06826','avaliador','2025-05-25 23:04:13'),(17,'0ee7799e4e8ab157c29ef975c212f055dcea4a0fc5bad81bbc3b0d3eb3b1dba9','avaliador','2025-05-25 23:04:51'),(18,'60be319a0e08e4aa685761032942204df37906a4c345cb24692e7e9fcee2e86d','avaliador','2025-05-25 23:04:53'),(19,'2ef5b76f1be49367a946a3aec12186189c58208b21b779f605dde28064b6da0b','avaliador','2025-05-25 23:05:09'),(20,'225285b871cff8d99f8f46c2f6e252265fa83df39f835d423bbeb1b95b721de8','avaliador','2025-05-25 23:05:10'),(21,'812d0b2bf1785738df3e61f37f2771392f22931c34303744d41d05a5e64f0f88','avaliador','2025-05-25 23:05:11'),(22,'6f92373cbb3c3ce28da58d27566adef176fbdd0c5363907304f3dfa1fa012dd4','avaliador','2025-05-25 23:05:26'),(24,'d5a5c9eb8ca798085dcbc8e714b8643e33416dc02eb6c9ef6906b656a6189c90','avaliador','2025-05-25 23:10:47'),(29,'b3bbf091585dad42169b05b59d16bfdec0dc19c235b56ba830a4eac4b57dd4bb','avaliador','2025-05-25 23:13:46'),(30,'0f6d3dd1adfbbbe92be096e276a48a9eb0b1f2552291cbf0cf0157d3db396e4d','avaliador','2025-05-25 23:13:47'),(31,'de8b0ccd63c398f09d39039ee60cbd1591c5f9465d2b05aadb64af0361e111e9','avaliador','2025-05-25 23:14:03'),(32,'803be47e53607e34183e180d4b99779671c95a4648373203f3f727269a2abc30','avaliador','2025-05-25 23:14:27'),(33,'111d908087ee28b0c0b9d1a62b9de3c02872e7ee8e62123bd7272dfc33b56d45','avaliador','2025-05-25 23:14:28'),(34,'da3b0c8a843a77807dbc9e28e5df4231d83c8eb6821b6327f08a6db3ed0226dc','avaliador','2025-05-25 23:14:28'),(35,'ecfad5c4646592a89993ea0dbfce9f7d17639a8abc22e97e024295e07bfa1414','avaliador','2025-05-26 18:38:50'),(36,'f62f01edf8a5485e613a2e5c0f151a0e5a9367924d504d864d47680a35e6a4cd','avaliador','2025-05-26 20:54:43');
/*!40000 ALTER TABLE `tokens_acesso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT 'Não informado',
  `tipo_usuario` varchar(50) DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `tipo` enum('aluno','avaliador','admin') DEFAULT 'aluno',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'João Silva','joao.silva@example.com','123','1990-05-20','SP','',NULL,NULL,'aluno'),(2,'calos','carlos@gmail.com','$2y$10$ZzKPCrjgIAX72AvQQH.cmeC3cSB2vbL1jkw/Qjyd/khMNQs.TbZ8m','2000-11-10',NULL,'Sao Paulo',NULL,NULL,'aluno'),(3,'gabriel ','gabriel@gmail.com','$2y$10$mv70irvFYsh3d2iLbKrHf.s9PhUATjCCMsLx2agPnTg8LdKH3fSlu',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(4,'josue','josue@gmail.com','$2y$10$pmqZk4Nv9z3kztdaC.OF9uGAoNTbTkCCnznlchjFEf6SYV8Ixg/5G',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(5,'isa','isa@gmail.com','$2y$10$V0CYrGsui1QSJ0vDKkHaSeFSNmC8RapVCZq0IOeT11PRLQBZoJ6pW',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(6,'rafa','rafa@gmail.com','$2y$10$yHUgBtvZEjDkFxTZfl.3P.0tDWfXL2quUnZZtRN5Tlrw1i12TqkWi',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(7,'gael','gael@gmail.com','$2y$10$qIOD9CGOAoCuh4K3mZYq6Orj/ggjrHjRFOCg5WSIKTaOHbqAcwS22',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(9,'ga','ga@gmail.com','$2y$10$XgezbMCXDKD0xO/wuVUh0e5XDSPBUrjxuEQBcQjhF/rim6AEkImd.',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(10,'das','dada@gmail.com','$2y$10$6AxUnvfr2sjrZ9uLOjYce.wzoMSWp89oO/QRQfmRua/mLsHEIAEae',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(11,'12','12@gmail.com','$2y$10$pE9D58yyi9kBpvhE8wq27uQgI87PLf/MVbtvYnPKmUNXFRKuc0pvq',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(12,'13','13@gmail.com','$2y$10$mqoZU2VplLxPA2Wq/gmKtu7xY5.VuOejuzvCnhGZE7nCLOsR1fCiC',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(13,'123','123@gmail.com','$2y$10$w/59o.w8Rh7.jjF1ZPG4U.XqGrRZ0oH7KZ5L6SGJKNAyqRCNnLBGC',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(14,'igor','igor@gmail.com','$2y$10$TbCuijw9tvrxbfLBqQ.j..5qzstHqK.qF9j621lvkIqNzgY64Upla',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(15,'pedro','p@gmail.com','$2y$10$OhKmIslthH3PVfPtukELSur0jhRk7TikpeWS4058dnkNQ5pbatShG',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(16,'dsada','dsa@gmail.com','$2y$10$wZXzWPQnbS1fau/3k8mMP.xQNQiId/w5w4RoUi6LW9ogFQ0fLeUou',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(17,'Pedro','pedro@gmail.com','$2y$10$uArVbF/YaBpNPYfXXjoC7OzpnggH5KbHdYuLUyBmhButKA9b8/vBO',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(18,'gabriel','gabriel1@gmail.com','$2y$10$PlFlVIIDNlFKmwdGF1Dhfudp.RyuSYWpKL8i0j4OydIWHOAryd8Ii',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(19,'henrique','henrique@gmail.com','$2y$10$sj7taTLTKkSCiOhB1EoOs.NCl7rbiXFxw3iPwk4qgZAo4CFBgkFpu',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(20,'123','123555@gmail.com','$2y$10$3rnks6QPrCOrsmN7DQ417OqWk70lD3ZGc27ColrQTnMm5qretaUwe',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(21,'euler','euler@gmail.com','$2y$10$5ijEe/6mx2tVzVrg4.AITeRi9Y3uXlm79K10FZ5LjTxv860xEro1K',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(22,'Pedro','12312321@gmail.com','$2y$10$jVXTXOBYVPEG32KeSe34C.6mf3rgHzF7hweGq2GHhI9m0wd0uoKMK',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(23,'3123131231','312313131213@gmail.com','$2y$10$gX4KA0V2vMVPeoITNa5ntOipEjgNvmr03YopRqDTJ4NPo1s2ygSSi',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(24,'carlos','231313131@gmail.com','$2y$10$EryrsnDdyUQxVCBHWC98HO7CMTN1H8EiU95f6uz0eWdYpw4xKcL9u',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(25,'Gabriel','gabriel1234@gmail.com','$2y$10$K83jJPuRRd1rnHiJCdUDNuuv7jH1DHhGiMTbQTIvoP3nQs9ytTot6',NULL,NULL,'Não informado',NULL,NULL,'aluno'),(26,'Administrador','admin@admin.com','$2y$10$p0UkRwz1lV24W0x8j8VYNOvJbCIwhZMKkri1CuygQEr6rIOkyAvJe',NULL,NULL,'Não informado',NULL,NULL,'admin'),(27,'Jose','joseteste@gmail.com','$2y$10$OJPBUKvh/7Uvcrk5.9.lDu2uhbhFhvUVW.P/kdFDlPT4MUXlfkedu',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(28,'miguel','miguel3131313@gmail.com','$2y$10$uwBLo78U69e7sZMi8MuL8uwoHvOk2LH8idSGZKHWMfJ2soAdAmxzG',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(29,'Micael da Silva Neves','micaelsilvaneves@gmail.com','$2y$10$TBSEN0iHsI5.s/9mjjKWiOtdY.OvtdSo3A8Rd0Dbh6hrGlIWZ.yVO',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(30,'miguel da siulva','migue@gmail.com','$2y$10$fNB/jD.o0/DvvcNnK9gzNOqv9iV1XTGkG7YB7ZraCUTr/SrEMI.PC',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(31,'henrique','henrique3414141@gmail.com','$2y$10$HOS1VDH53G.CJrJEnKWs6uYmU4aVDO2KrMRjawnWqpGx36RvI/N6S',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(32,'Jose','fjakfa@gmail.com','$2y$10$WMoOD0Ts8ir9j0XEuu049.7GTlTb0zvBL41xIsGRk2XNvzlx.dYiS',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(33,'Rogerio Rocha ','rogeriorocha@gmail.com','$2y$10$jS5rz6kPXyBoaAzytGy/WehlYOveUgxrQ1I6OJzXgJlCAZUGBwc3.',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(34,'infoanioagioaga','j@gmail.com','$2y$10$BbbwnZ2pFEoyYw.GQgPRBuNzfT61hoTGh4vOUgaUEe0U/3tTX6.WG',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(35,'dasdadadadad','s@gmail.com','$2y$10$Q5QMG8subAg5utNJA4UEGuMWIn0/9HBe.64bwF8YQFlmHGaWnGZ5.',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(36,'ewewewe','ewew@gmail.com','$2y$10$Gf01TpLXpy/GYuRRi7JMDeJ2zNfPwm/ksEn/AkXVl79/T93lgvylW',NULL,NULL,'Não informado',NULL,NULL,'avaliador'),(37,'miguel','miguel@gmail.com','$2y$10$ti3PPhfZynIc2WynhxEbIujmv60qJRyN/6pou3h2qvGAZZzWQesdK',NULL,NULL,'Não informado',NULL,NULL,'avaliador');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-02 19:43:08
