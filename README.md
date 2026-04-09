# Login

```usuario
admin@empresa.cl
```
```contraseña
password
```
--------------------------------
```usuario
usuario@empresa.cl
```
```contraseña
password
```
# Base de Datos: Sistema de Remuneraciones

Este script SQL crea y pobla una base de datos llamada **`remuneraciones`**, destinada a gestionar la información laboral, previsional y de salud de los trabajadores de una organización.

---

##  Estructura de Tablas

```sql
USE remuneraciones;
```

```
DROP DATABASE IF EXISTS remuneraciones;
```
```
CREATE DATABASE remuneraciones;
```
```
USE remuneraciones;
```

## 1. CARGO
```
CREATE TABLE cargo (
    id_cargo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cargo VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
```

## 2. SISTEMA SALUD
```
CREATE TABLE sistema_salud (
    id_salud INT AUTO_INCREMENT PRIMARY KEY,
    nombre_salud VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
```

## 3. TIPOS CONTRATO
```
CREATE TABLE tipos_contrato (
    id_tipo_contrato INT AUTO_INCREMENT PRIMARY KEY,
    nombre_contrato VARCHAR(50) NOT NULL
) ENGINE=InnoDB;
```

## 4. AFP (Incluye porcentaje para cálculos)

```
CREATE TABLE afp (
    id_afp INT AUTO_INCREMENT PRIMARY KEY,
    nombre_afp VARCHAR(50) NOT NULL,
    porcentaje_descuento DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB;
```

## 5. TRABAJADOR (Aquí definimos los montos FIJOS pactados)
```
CREATE TABLE trabajador (
    rut_trabajador VARCHAR(12) PRIMARY KEY,
    id_cargo INT,
    id_tipo_contrato INT,
    id_afp INT,
    id_salud INT,
    nombre_completo VARCHAR(100) NOT NULL,
    sueldo_base_fijo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    colacion DECIMAL(12,2) NOT NULL DEFAULT 0.00, -- Monto pactado
    transporte DECIMAL(12,2) NOT NULL DEFAULT 0.00, -- Monto pactado
    fecha_inicio_contrato DATE NOT NULL,
    fecha_termino_contrato DATE NULL,
    FOREIGN KEY (id_cargo) REFERENCES cargo(id_cargo),
    FOREIGN KEY (id_tipo_contrato) REFERENCES tipos_contrato(id_tipo_contrato),
    FOREIGN KEY (id_afp) REFERENCES afp(id_afp),
    FOREIGN KEY (id_salud) REFERENCES sistema_salud(id_salud)
) ENGINE=InnoDB;
```

## 6. LIQUIDACION (Registro histórico de lo pagado)
```
-- 2. Crear la tabla nueva con horas extras incluidas
CREATE TABLE liquidacion (
    id_liquidacion        INT AUTO_INCREMENT PRIMARY KEY,
    rut_trabajador        VARCHAR(12),
    mes_periodo           INT,
    anio_periodo          INT,
    nombre_empleador      VARCHAR(100) DEFAULT 'Colegio Ejemplo',
    dias_trabajados       INT DEFAULT 30,
    sueldo_base_mes       DECIMAL(12,2),
    gratificacion         DECIMAL(12,2),
    horas_extra_cantidad  INT DEFAULT 0,
    horas_extra_monto     DECIMAL(12,2) DEFAULT 0.00,
    colacion              DECIMAL(12,2),
    transporte            DECIMAL(12,2),
    cotiz_previsional     DECIMAL(12,2),
    cotiz_salud           DECIMAL(12,2),
    seguro_cesantia       DECIMAL(12,2),
    impuesto_unico        DECIMAL(12,2) DEFAULT 0,
    liquido_a_pagar       DECIMAL(12,2),
    fecha_emision         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rut_trabajador) REFERENCES trabajador(rut_trabajador)
) ENGINE=InnoDB;
```

## 7. USUARIOS (Control de acceso al sistema)
```
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') NOT NULL DEFAULT 'admin',
    activo TINYINT NOT NULL DEFAULT 1, -- Se eliminó el (1) para evitar el Warning 1681
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL
) ENGINE=InnoDB;
```

```
INSERT INTO usuarios (nombre_usuario, email, password_hash, rol, activo)
VALUES (
    'admin',
    'admin@empresa.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1
);
```
```
INSERT INTO usuarios (nombre_usuario, email, password_hash, rol, activo)
VALUES (
    'usuario',
    'usuario@empresa.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'supervisor',
    1
);
```

##-- ============================================================
##-- TABLA IMPUESTO ÚNICO DE SEGUNDA CATEGORÍA
##-- Agregar a tu base de datos remuneraciones
##-- ============================================================
```
CREATE TABLE IF NOT EXISTS impuesto_unico (
    id_tramo        INT AUTO_INCREMENT PRIMARY KEY,
    desde           DECIMAL(14,2) NOT NULL,
    hasta           DECIMAL(14,2) NULL,        -- NULL = sin límite superior
    factor          DECIMAL(5,4) NOT NULL,
    cantidad_rebajar DECIMAL(14,2) NOT NULL,
    tasa_efectiva   VARCHAR(20) NOT NULL
) ENGINE=InnoDB;
```
##-- Datos vigentes
```
INSERT INTO impuesto_unico (desde, hasta, factor, cantidad_rebajar, tasa_efectiva) VALUES
(0,            908469.00,    0,      0,           'Exento'),
(908469.01,    2018820.00,   0.04,   36338.76,    '2,20%'),
(2018820.01,   3364700.00,   0.08,   117091.56,   '4,52%'),
(3364700.01,   4710580.00,   0.135,  302150.06,   '7,09%'),
(4710580.01,   6056460.00,   0.23,   749655.16,   '10,62%'),
(6056460.01,   8075280.00,   0.304,  1197833.20,  '15,57%'),
(8075280.01,   20861140.00,  0.35,   1569296.08,  '27,48%'),
(20861140.01,  NULL,         0.4,    2612353.08,  'Más de 27,48%');
```
##-- Agregar columna impuesto_unico a la tabla liquidacion (si no existe)
```
ALTER TABLE liquidacion
    ADD COLUMN IF NOT EXISTS impuesto_unico DECIMAL(12,2) DEFAULT 0;
```



-- ==========================================
-- INSERCIÓN DE DATOS MAESTROS
-- ==========================================
```
INSERT INTO cargo (nombre_cargo) VALUES ('Docente'), ('Administrativo'), ('Auxiliar');
```
```
INSERT INTO sistema_salud (nombre_salud) VALUES ('Fonasa'), ('Isapre Colmena'), ('Isapre Banmedica');
```
```
INSERT INTO tipos_contrato (nombre_contrato) VALUES ('Indefinido'), ('A plazo fijo'), ('Práctica');
```
```
INSERT INTO afp (nombre_afp, porcentaje_descuento) VALUES 
('Capital', 11.44), ('Cuprum', 11.44), ('Habitat', 11.27), 
('PlanVital', 11.16), ('ProVida', 11.45), ('Modelo', 10.58), ('Uno', 10.46);
```