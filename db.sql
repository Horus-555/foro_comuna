DROP DATABASE IF EXISTS foro_comuna;
CREATE DATABASE foro_comuna CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE foro_comuna;

-- --------------------------------------------
-- Tabla: usuarios
-- --------------------------------------------

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rut VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    clave VARCHAR(255) NOT NULL,
    role ENUM('usuario','admin') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear un usuario administrador de prueba
INSERT INTO usuarios (nombre, apellido, rut, email, clave, role) VALUES
('Admin', 'Principal', '11.111.111-1', 'admin@admin.cl', 
 -- contraseña: admin123
 '$2y$10$zN2n0Yz2O8pPHdKqh1lQbO0bZJQKc5jH9oUkVsL9WyE1ONlC5ufUy',
 'admin');

-- --------------------------------------------
-- Tabla: categorias
-- --------------------------------------------

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Categorías por defecto
INSERT INTO categorias (nombre) VALUES
('Seguridad'),
('Eventos'),
('Mascotas'),
('Servicios'),
('Alerta'),
('Recomendaciones');

-- --------------------------------------------
-- Tabla: posts
-- --------------------------------------------

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    imagen VARCHAR(255) NULL,
    direccion VARCHAR(255) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- --------------------------------------------
-- Tabla: comentarios
-- --------------------------------------------

CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

