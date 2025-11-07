# 📊 Base de Datos: Sistema de Remuneraciones

Este script SQL crea y pobla una base de datos llamada **`remuneraciones`**, destinada a gestionar la información laboral, previsional y de salud de los trabajadores de una organización.

---

##  Estructura de Tablas

```sql
USE remuneraciones;
```
### 1. Tabla `CARGO`
```
CREATE TABLE cargo (
    Id_Cargo INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Cargo VARCHAR(50) NOT NULL
);
```
### 2. Tabla `SISTEMA_SALUD`
```
CREATE TABLE sistema_salud (
    Id_Salud INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Salud VARCHAR(50) NOT NULL
);
```
### 3. Tabla `TIPOS_CONTRATO`
```
CREATE TABLE tipos_contrato (
    Id_tipo_contrato INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Contrato VARCHAR(50) NOT NULL
);
```
### 4. Tabla `AFP`
```
CREATE TABLE afp (
    Id_Afp INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Afp VARCHAR(50) NOT NULL,
    Porcentaje_Descuento DECIMAL(4, 2) NOT NULL -- Porcentaje de descuento de la AFP.
);
```
### 5. Tabla `TRABAJADOR`
```
CREATE TABLE trabajador (
    Rut_Trabajador VARCHAR(12) PRIMARY KEY,
    Id_Cargo INT NOT NULL,
    Id_tipo_contrato INT NOT NULL,
    Id_Afp INT NOT NULL,
    Id_Salud INT NOT NULL,
    Nombre_Completo VARCHAR(100) NOT NULL,
    Carga INT,
    Sueldo_Base_Fijo DECIMAL(10, 2) NOT NULL,
    Fecha_Inicio_Contrato DATE NOT NULL,
    Fecha_Termino_Contrato DATE,

    FOREIGN KEY (Id_Cargo) REFERENCES CARGO(Id_Cargo),
    FOREIGN KEY (Id_tipo_contrato) REFERENCES TIPOS_CONTRATO(Id_tipo_contrato),
    FOREIGN KEY (Id_Afp) REFERENCES AFP(Id_Afp),
    FOREIGN KEY (Id_Salud) REFERENCES SISTEMA_SALUD(Id_Salud)
);
```

### 6. Tabla `lIQUIDACION`
```
CREATE TABLE liquidacion (
    Id_Liquidacion INT PRIMARY KEY AUTO_INCREMENT,
    Rut_Trabajador VARCHAR(12) NOT NULL,
    Mes_Periodo INT NOT NULL,
    Anio_Periodo INT NOT NULL,
    Nombre_Empleador VARCHAR(100),
    Dias_Trabajados INT NOT NULL,
    Valor_Uf DECIMAL(10, 2),
    Sueldo_Base_Mes DECIMAL(10, 2),
    Gratificacion DECIMAL(10, 2) DEFAULT 0.00,
    Colacion DECIMAL(10, 2) DEFAULT 0.00,
    Cotiz_Previsional DECIMAL(10, 2) DEFAULT 0.00,
    Cotiz_Salud DECIMAL(10, 2) DEFAULT 0.00,
    Seguro_Cesantia DECIMAL(10, 2) DEFAULT 0.00,
    Otros_Descuentos DECIMAL(10, 2) DEFAULT 0.00,
    Imp_Prev_Salud DECIMAL(10, 2) DEFAULT 0.00,
    Imp_Cesantia DECIMAL(10, 2) DEFAULT 0.00,
    Base_Tributable DECIMAL(10, 2) DEFAULT 0.00,
    Liquido_a_Pagar DECIMAL(10, 2) DEFAULT 0.00,

    UNIQUE KEY uk_liquidacion_periodo (Rut_Trabajador, Mes_Periodo, Anio_Periodo),
    FOREIGN KEY (Rut_Trabajador) REFERENCES TRABAJADOR(Rut_Trabajador)
);

```

##  Insert de Datos
### 1. Tabla `CARGO`
```
INSERT INTO cargo (Nombre_Cargo) VALUES
('Gerente General'),
('Analista de Remuneraciones'),
('Ejecutivo de Ventas');

```
### 2. Tabla `SISTEMA_SALUD`
```
INSERT INTO sistema_salud (Nombre_Salud) VALUES
('Fonasa'),
('Isapre Colmena'),
('Isapre Banmedica');

```
### 3. Tabla `TIPOS_CONTRATO`
```
INSERT INTO sistema_salud (Nombre_Salud) VALUES
('Fonasa'),
('Isapre Colmena'),
('Isapre Banmedica');

```
### 4. Tabla `AFP`
```
INSERT INTO afp (Nombre_Afp, Porcentaje_Descuento) VALUES
('AFP Capital', 11.44), 
('AFP Cuprum', 11.44), 
('AFP Habitat', 11.27),
('AFP PlanVital', 11.16), 
('AFP ProVida', 11.45), 
('AFP Modelo', 10.58),
('AFP Uno', 10.46);

```
### 5. Tabla `TRABAJADOR`
```
INSERT INTO trabajador (
    Rut_Trabajador, Id_Cargo, Id_tipo_contrato, Id_Afp, Id_Salud, 
    Nombre_Completo, Carga, Sueldo_Base_Fijo, Fecha_Inicio_Contrato, Fecha_Termino_Contrato
) VALUES 
(
    '18765432-1', 
    2, 
    1, 
    1, 
    1, 
    'Juan Pérez Soto', 
    2,
    800000.00, 
    '2020-03-01', 
    NULL
),
(
    '15123456-K', 
    3, 
    2, 
    3, 
    2, 
    'María Lopez Díaz', 
    0, 
    650000.00, 
    '2024-01-15', 
    '2024-06-15'
);

```
### 6. Tabla `LIQUIDACION`
```
INSERT INTO liquidacion (
    Rut_Trabajador, Mes_Periodo, Anio_Periodo, Nombre_Empleador, Dias_Trabajados, Valor_Uf, 
    Sueldo_Base_Mes, Gratificacion, Colacion, Cotiz_Previsional, Cotiz_Salud, 
    Seguro_Cesantia, Otros_Descuentos, Imp_Prev_Salud, Imp_Cesantia, Base_Tributable, Liquido_a_Pagar
) VALUES
(
    '18765432-1', 
    10, 
    2024, 
    'Mi Empresa S.A.', 
    30, 
    37000.00,
    800000.00, 
    200000.00, 
    50000.00, 
    114400.00,
    70000.00, 
    0.00, 
    0.00, 
    0.00, 
    930000.00, 
    750000.00
);


```

###  Importante si se va a correr el proyecto en local hay que cambiar las rutas relativas del css y las rutas de navegación.