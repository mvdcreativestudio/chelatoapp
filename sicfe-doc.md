# Guía de Integración SICFE — Uruguay

> © 2025 SICFE SA. Todos los derechos reservados.  
> *Documento clasificado como "Privado y Confidencial". Su uso queda restringido a los fines para los que SICFE lo ha facilitado.*

---

## Tabla de Contenidos

- [Introducción](#introducción)
- [Plan de trabajo](#plan-de-trabajo)
  - [Tareas del sistema Cliente](#tareas-del-sistema-cliente)
  - [Tareas del sistema SICFE Middleware](#tareas-del-sistema-sicfe-middleware)
  - [Responsabilidades a definir en conjunto](#responsabilidades-a-definir-en-conjunto)
- [Plazos](#plazos)
- [Interfaces](#interfaces)
  - [API SOAP-XML](#api-soap-xml)
  - [Emisión](#emisión)
    - [EnvioCFE](#enviocfe)
    - [EnvioCFESinFirmar](#enviocfesinfrmar)
    - [ImportarCFE](#importarcfe)
    - [EnvioCFEConReceptor](#enviocfeconreceptor)
    - [ObtenerEstadoCFE](#obtenerestadocfe)
    - [ValidarEnvioCFE](#validarenviocfe)
    - [ReservarNro](#reservarnro)
    - [ConfirmarCFE](#confirmarcfe)
    - [AnularNumeracion](#anularnumeracion)
    - [AnularRango (deprecado)](#anularrango-deprecado)
  - [Recepción](#recepción)
    - [ObtenerCFEsRecibidos](#obtenercfesrecibidos)
    - [ObtenerCFEsRecibidosExtendido](#obtenercfesrecibidosextendido)
    - [ConfirmarCFERecibido](#confirmarcferecibido)
    - [DesconfirmarCFERecibido](#desconfirmarcferecibido)
    - [AceptarCFERecibido](#aceptarcferecibido)
    - [RechazarCFERecibido](#rechazarcferecibido)
    - [ObtenerEstadoDGIRecibido](#obtenerestadodgirecibido)
  - [Impresión](#impresión)
    - [ObtenerPDF](#obtenerpdf)
    - [ObtenerPDFUnico](#obtenerpdfu-nico)
    - [Reimprimir](#reimprimir)
    - [ObtenerRecursosImpresion](#obtenerrecursosimpresion)
    - [ObtenerTemplatesImpresion](#obtenertemplatesimpresion)
    - [ObtenerPDFSinProcesar](#obtenerpdfs-inprocesar)
  - [Otros](#otros-servicios)
    - [ActualizarReceptoresNoElectronicos](#actualizarreceptoresnoelectronicos)
    - [ObtenerReceptoresNoElectronicos](#obtenerreceptoresnoelectronicos)
    - [ConsolidarComprobantes](#consolidarcomprobantes)
    - [EsEmisorReceptorElectronico](#esemisorreceptorelectronico)
    - [ObtenerCAE](#obtenercae)
    - [ObtenerCFEPorID](#obtenercfepori-d)
    - [ObtenerCFEPorReferencia](#obtenercfeporreferencia)
    - [ObtenerCFEPorReferenciaConRespuestaDeEnvio](#obtenercfeporreferenciaconrespuestadeenvio)
    - [ObtenerClientesElectronicos](#obtenerclienteselectronicos)
    - [ObtenerDatosDeEmisorReceptor](#obtenerdatosdeemisorreeceptor)
    - [ObtenerInfoCertificados](#obtenerinfocertificados)
    - [ObtenerProveedoresElectronicos](#obtenerproveedoreselectronicos)
    - [ObtenerVersion](#obtenerversion)
    - [ObtenerXML_DGI](#obtenerxml_dgi)
    - [PingSICFEEmisor](#pingsicfeemisor)
    - [AgregarCAE](#agregarcae)
    - [ReenvioComprobante](#reenviocomprobante)
    - [ObtenerDatosRUCDGI](#obtenerdatosrucdgi)
    - [AgregarLogo](#agregarlogo)
    - [AgregarCertificado](#agregarcertificado)
  - [File System](#file-system)
- [Tiempos de envío](#tiempos-de-envío)
- [Formato CFE](#formato-cfe)
- [Ejemplos con SOAP UI](#ejemplos-con-soap-ui)
  - [Emisión — Ejemplos](#emisión--ejemplos)
  - [Recepción — Ejemplos](#recepción--ejemplos)
  - [Impresión — Ejemplos](#impresión--ejemplos)
  - [Otros — Ejemplos](#otros--ejemplos)
- [Otros recursos](#otros-recursos)
  - [Links útiles](#links-útiles)
  - [Caracteres a escapar en XML](#caracteres-a-escapar-en-xml)
  - [Formato HTML en Adenda](#formato-html-en-adenda)

---

## Introducción

Este documento tiene como objetivo presentar todos los aspectos a tener en cuenta para comunicarse correctamente con la solución **SICFE Middleware**.

Se presentarán las siguientes pautas para comenzar a trabajar con SICFE:

- **Diagramas de flujo** de las distintas operaciones.
- **Ejemplos de uso** tanto de la interfaz mediante intercambio de archivos como por web services utilizando la herramienta **SOAP UI**.
- Tablas con los distintos **códigos de respuesta** que puede devolver SICFE.
- Especificación del **formato de los Comprobantes Fiscales Electrónicos (CFEs)**.

---

## Plan de trabajo

Las principales tareas técnicas que se deben realizar para poder ingresar al régimen de facturación electrónica son:

- Generar CFEs en un formato XML establecido por la DGI.
- Numerar los CFEs utilizando la numeración (CAE) asignada por la DGI.
- Aplicar la firma electrónica a los CFEs.
- Realizar el envío de los CFEs a la DGI a través de Web Services y a los clientes a través de correo electrónico.
- Realizar el envío de los CFEs a los clientes de la empresa que sean receptores electrónicos.
- Procesar los CFEs recibidos que son enviados por los proveedores de la empresa que son emisores electrónicos.
- Dar respuesta (aceptación o rechazo) a los CFEs emitidos por otras empresas.
- Generar representaciones impresas que cumplan los requisitos de formato de la DGI.
- Generar código QR de forma dinámica con datos del CFE.

### Tareas del sistema Cliente

- **Generar los XML de los CFEs** utilizando un formato estándar establecido por la DGI.
- Identificar cada transacción con un **número único** para que SICFE pueda identificar los envíos duplicados (puede utilizarse el número interno del ERP para cada transacción).
- **Enviar el CFE a SICFE** a través de Web Services o intercambio de archivos.
- Procesar la respuesta de SICFE, **almacenando el número fiscal** asignado y los datos obligatorios para la impresión.
- Realizar la **impresión de los CFEs**, generando la representación impresa o utilizando la provista por SICFE.
- **Consultar el estado final de los CFEs** (aceptación o rechazo por parte de la DGI).
- *(Opcional)* Obtener los CFEs de proveedores de la empresa a través de las API de SICFE, impactarlos en la contabilidad y procesos de la empresa; también puede utilizarse la API para aceptarlos o rechazarlos.
- *(Opcional)* Encargarse de aplicar la numeración fiscal (CAE) a cada documento. SICFE lo hace por defecto, pero puede configurarse para que lo haga el sistema de facturación.

> **Importante:** El sistema de facturación deberá implementar y procesar las respuestas, como mínimo, del siguiente conjunto de operaciones:
> - `EnvioCFE`
> - `ObtenerEstadoCFE`

### Tareas del sistema SICFE Middleware

Toda comunicación con el sistema se realiza a través de un conjunto de **Web Services** o carpetas en el sistema de intercambio de archivos que exponen operaciones. Todas ellas comparten un formato de respuesta que consta de:

- Un código de éxito o error.
- Una descripción del resultado.
- El resultado en sí.

A través de estas interfaces es posible emitir CFEs, obtener PDFs con las representaciones impresas de los mismos, recibir CFEs de proveedores, entre otras.

Con el fin de facilitar la integración, SICFE brinda:

- Un ambiente de prueba completamente funcional, y soporte técnico en el mismo, para ayudar a descubrir y solucionar los errores en la comunicación.
- Apoyo en todo el proceso de generación de los CFEs, dando soporte y ayuda en la corrección de posibles errores de formato.
- Ejemplos de distintos tipos de CFE.
- Documentación técnica de todas las operaciones de las interfaces.

### Responsabilidades a definir en conjunto

Se deberá repasar y definir lo siguiente antes de comenzar los trabajos de integración:

- **¿Qué sistema generará la representación gráfica de los CFEs?**  
  Si se utiliza la de SICFE, se provee un formato estándar sin costo que cumple todos los requisitos de DGI.
- **¿Se imprimirá a una impresora? Si es así, ¿qué sistema lo hará?**  
  Debe definirse qué sistema imprimirá ya que tiene impactos en la instalación del sistema. Si se requiere que SICFE imprima, se deberá realizar una instalación local del sistema para poder llegar a las impresoras de red.
- **¿Qué interfaz se utilizará? ¿Web Services o intercambio de archivos?**  
  La opción por Web Services es la más completa, rápida y segura.
- **¿Qué sistema aplicará la numeración a los CFEs?**  
  Si se encarga el sistema de facturación, deberá mantener los CAEs correspondientes, controlar los vencimientos y falta de números.
- **¿El sistema de facturación numerará y firmará los CFEs?**  
  Si es así, SICFE solamente se encarga de almacenarlos y enviarlos a los respectivos receptores, sin realizar validaciones sobre los mismos.

---

## Plazos

Las empresas que deciden ingresar al régimen de facturación electrónica deben pasar por varias etapas antes de convertirse en emisoras electrónicas.

Las etapas son: **Postulación**, **Homologación** y **Producción**.

- **Postulación:** El personal de Sicfe asesora en lo referido a los trámites administrativos que la empresa debe llevar a cabo frente a la DGI. Se deberá realizar la creación de usuarios en DGI y la postulación. La Postulación **deberá realizarse antes de la fecha tope** establecida por DGI para la empresa.

- **Homologación:** Sicfe trabajará en conjunto con el personal de la empresa para que puedan desarrollar las comunicaciones y probarlas de forma satisfactoria. Nuestro equipo lo asesorará para obtener el certificado digital necesario para realizar la emisión de los comprobantes fiscales electrónicos. A su vez se asesorará en aspectos contables referentes a la normativa y los cambios que afectan a la empresa. Hay un **plazo máximo de un mes** para completar esta etapa, a partir de la postulación.

- **Producción:** Se deberá configurar el sistema de la empresa para que consuma los servicios de SICFE Middleware definitivos. Es recomendable llegar a esta etapa con **toda la operativa de la empresa probada de forma correcta y exhaustiva** con el fin de evitar errores.

Las empresas que deciden realizar un cambio de proveedor, deberán desarrollar las comunicaciones, probarlas de forma satisfactoria y luego coordinar, en conjunto con el equipo de Sicfe, el inicio en el ambiente de Producción.

---

## Interfaces

### API SOAP-XML

El protocolo de la API utilizado es **SOAP**, esto significa que se utiliza un estándar basado en XML para intercambiar información estructurada en la implementación de servicios web.

La interfaz mediante Web Services SOAP-XML contiene las siguientes operaciones:

| **EMISIÓN** | **RECEPCIÓN** | **IMPRESIÓN** |
|---|---|---|
| EnvioCFE | ObtenerCFEsRecibidos | ObtenerPDF |
| EnvioCFESinFirmar | ObtenerCFEsRecibidosExtendido | Reimprimir |
| ImportarCFE | ConfirmarCFERecibido | ObtenerRecursosImpresion |
| EnvioCFEConReceptor | DesconfirmarCFERecibido | ObtenerTemplatesImpresion |
| ObtenerEstadoCFE | AceptarCFERecibido | ObtenerPDFSinProcesar |
| ValidarEnvioCFE | RechazarCFERecibido | |
| ReservarNro | ObtenerEstadoDGIRecibido | |
| ConfirmarCFE | | |
| AnularNumeracion | | |
| ~~AnularRango~~ (deprecado) | | |

**OTROS:**

ActualizarReceptoresNoElectronicos, ObtenerReceptoresNoElectronicos, ConsolidarComprobantes, EsEmisorReceptorElectronico, ObtenerCAE, ObtenerCFEPorID, ObtenerCFEPorReferencia, ObtenerCFEPorReferenciaConRespuestaDeEnvio, ObtenerClientesElectronicos, ObtenerDatosDeEmisorReceptor, ObtenerInfoCertificados, ObtenerProveedoresElectronicos, ObtenerVersion, ObtenerXML_DGI, PingSICFEEmisor, AgregarCAE, ReenvioComprobante, ObtenerDatosRUCDGI

#### Formato de respuesta común (SICFERespuesta)

Todos los servicios retornan además de los datos de negocio un código y una descripción de respuesta.

| Nombre | Tipo | Descripción |
|---|---|---|
| `Codigo` | int | Código del resultado de la operación. `0` (cero) indica que la operación ha sido exitosa. |
| `Descripcion` | string | Descripción del código de error. |

#### Diagrama de flujo general

El flujo de envío de un CFE a SICFE Middleware y su posterior consulta de estado involucra tres actores principales: **ERP**, **SICFE** y **DGI**.

```
ERP                          SICFE                              DGI
 |                              |                                |
 |-- Generación del XML ------> |                                |
 |   (EnvioCFE)                 |                                |
 |                              |-- Validación sintáctica        |
 |                              |   y semántica                  |
 |                              |-- Numeración (CAE)             |
 |                              |-- Firma XML                    |
 |                              |-- Almacena el CFE              |
 |                              |                                |
 |<-- Datos del CFE (QR, XML) --|                                |
 |   numerado y firmado         |                                |
 |                              |-- Envío a DGI ------------->  |
 |                              |                                |-- Procesamiento
 |                              |<-- 1ra actualización estado -- |
 |                              |-- Actualización estado CFE     |
 |                              |                                |
 |-- ObtenerEstadoCFE --------> |                                |
 |<-- Estado definitivo --------|                                |
 |                              |                                |
FIN
```

---

## Emisión

### EnvioCFE

Servicio utilizado para enviar CFEs en formato XML a SICFE. Se realizan validaciones de sintaxis y semántica del CFE, luego se numera y se firma. La respuesta devuelve los datos obligatorios para la impresión del CFE (QR, link QR, CAE).

Se conservan algunos parámetros deprecados para mantener la compatibilidad con sistemas que utilizan esta operación.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Permite identificar la terminal desde la que se genera el comprobante. Permite manejar numeración independiente por terminal de emisión. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. |
| `referenciaERP` | string | Identificador de la transacción, permite reconocer envíos duplicados. Se sugiere utilizar el número interno del ERP. Su valor no puede exceder los 50 caracteres. |
| ~~`referenciaERP2`~~ | string | **Deprecado.** |
| `devolverQR` | boolean | Determina si se devuelve el QR. |
| `sizeQR` | int | Tamaño del QR. Usar `22` o `30` (corresponde a los tamaños mínimos y máximos especificados por DGI, en mm). |
| ~~`imprime`~~ | int | **Deprecado.** |
| ~~`recurso`~~ | string | **Deprecado.** |
| `template` | string | *Opcional.* Nombre del template para la generación de representaciones gráficas en PDF. |
| `devolverXML` | boolean | Si se debe devolver el XML del CFE (firmado y numerado). Útil si el ERP desea almacenar el CFE completo y final. |
| ~~`erpPideValidacion`~~ | boolean | **Deprecado.** |
| `version` | string | Indicador de versión de XSDs de DGI para realizar la validación del CFE. Enviar vacío para usar la última versión. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `EsReceptorElectronico` | boolean | Indica si el receptor es electrónico. |
| `IdCFE.Numero` | int | Número del CFE. |
| `IdCFE.Serie` | string | Serie del CFE. |
| `IdCFE.Tipo` | int | Tipo del CFE. |
| `IdCFE.observado` | int | Sólo CFC (Contingencia). Indica cuántas veces se ha observado. |
| `IdCFE.rucemisor` | string | RUC del emisor del CFE. |
| `ImagenQR` | string | Imagen en PNG del código QR codificada en base64. |
| `LinkQR` | string | Enlace al sitio web de DGI con los datos del CFE. |
| `datosCAE.dnro` | int | Nº inicial del CAE. |
| `datosCAE.fvto` | datetime | Fecha de vencimiento del CAE. |
| `datosCAE.hnro` | int | Nº final del CAE. |
| `datosCAE.nauto` | decimal | Nº de autorización del CAE. |
| `hash` | string | Código hash del CFE (código de seguridad). |
| `xml` | string | XML del CFE con firma digital y CAE aplicados. |

> **Importante:**
> - Código `0` indica que un envío ha sido exitoso.
> - Código `100009` indica que el CFE que se está intentando enviar ya existe en Sicfe, debido a que se está utilizando la misma `referenciaERP`. Si se desea hacer un envío nuevo, utilizar una nueva `referenciaERP`. Si se quieren traer los datos de un envío anterior, utilizar la misma `referenciaERP`.

---

### EnvioCFESinFirmar

Ídem a `EnvioCFE`, con la diferencia de que se **recibe el CFE firmado y numerado**. Sicfe igualmente realiza las validaciones correspondientes.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Permite identificar la terminal desde la que se genera el comprobante. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. Debe estar firmado y numerado. |
| `version` | string | Indicador de versión de XSDs de DGI. Enviar vacío para usar la última versión. |
| `refErp` | string | Identificador de la transacción, permite reconocer envíos duplicados. Se sugiere utilizar el número interno del ERP. |

#### Salida

Ídem a la salida de `EnvioCFE`.

---

### ImportarCFE

Facilita la **importación de CFE provenientes de otros sistemas** (ya firmados y numerados) y permite configurar mediante parámetros si se deben realizar los envíos correspondientes a los receptores, reportes diarios, entre otros.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. Debe estar firmado y numerado. |
| `versionXSD` | string | Indicador de versión de XSDs de DGI. Enviar vacío para usar la última versión. |
| `referenciaERP` | string | Identificador de la transacción, permite reconocer envíos duplicados. |
| `validarCFE` | bool | Indica si se valida el CFE o no. |
| `generarReporteDiario` | bool | Indica si se genera el reporte diario o no. |
| `enviarRE` | bool | Indica si se envía al receptor electrónico o no. |
| `enviarRNE` | bool | Indica si se envía al receptor no electrónico o no. |
| `enviarMandante` | bool | Indica si se envía al mandante o no. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### EnvioCFEConReceptor

Similar al servicio `EnvioCFE`, con la diferencia de que en este caso se puede especificar una o varias casillas de correo para el envío de la representación gráfica en PDF.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Identificación de terminal de emisión. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. |
| `referenciaERP` | string | Identificador de la transacción. |
| `devolverQR` | boolean | Determina si se devuelve el QR. |
| ~~`imprime`~~ | int | **Deprecado.** |
| ~~`impresora`~~ | string | **Deprecado.** |
| `template` | string | *Opcional.* Nombre del template para PDF. |
| `versionXSD` | string | Indicador de versión de XSDs de DGI. Enviar vacío para usar la última versión. |
| `correo` | string | Lista de correos. Separados por `,` (coma). |
| `fechaEnvioCorreo` | datetime | Fecha en la que los correos serán enviados al receptor. Formato: `AAAA-MM-DD` o `AAAA-MM-DDTHH:MM:SS`. |

#### Salida

Ídem a la salida de `EnvioCFE`.

---

### ObtenerEstadoCFE

Devuelve el **estado respecto a DGI** de un CFE emitido. Puede estar Pendiente, Enviado, Aceptado o Rechazado, entre otros.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `pUsuario` | string | Nombre de usuario asignado al ERP. |
| `pClave` | string | Contraseña del usuario asignado al ERP. |
| `pTenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `pIDCFE.Numero` | int | Número del CFE. |
| `pIDCFE.Serie` | string | Serie del CFE. |
| `pIDCFE.Tipo` | int | Tipo del CFE. |
| `pIDCFE.observado` | int | Indica cantidad de observados. Utilizar `1` para todos los tipos de CFE que no sean Contingencia. |
| `pIDCFE.rucemisor` | string | RUC del emisor. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `CodRechazo` | string | Código de rechazo (cuando `Estado = "BE"`). |
| `Estado` | string | Estado del CFE (ver tabla). |
| `MotRechazo` | string | Descripción del código de rechazo. |

**Valores de Estado:**

| Código | Descripción |
|---|---|
| `AE` | CFE aceptado por DGI. |
| `BE` | CFE rechazado por DGI. |
| `CE` | CFE observado por DGI. |
| `PE` | CFE pendiente de envío a DGI. |
| `EN` | CFE enviado a DGI. |
| `RE` | CFE rechazado por sobre por DGI. |
| `NA` | eTicket < 10.000 UI, no aplica envío a DGI. |
| *(vacío)* | El CFE está en SICFE pero aún no ha sido procesado. |

**Valores de CodRechazo:**

| Código | Descripción |
|---|---|
| `E01` | Tipo y Nº de CFE ya fue reportado como anulado. |
| `E02` | Tipo y Nº de CFE ya existe en los registros. |
| `E03` | Tipo y Nº de CFE no se corresponden con el CAE. |
| `E04` | Firma electrónica no es válida. |
| `E05` | No cumple validaciones de formato CFE. |
| `E07` | Fecha Firma de CFE no se corresponde con fecha CAE. |
| `E08` | No coinciden RUC emisor de CFE y Complemento fiscal. |
| `E09` | Tipo de CFE recibido no figura en la base de CFE certificados. |

> **Importante:** `CodRechazo` y `MotRechazo` tienen valores únicamente cuando `Estado = "BE"`.

---

### ValidarEnvioCFE

Servicio que **únicamente realiza las validaciones de formato del CFE**. No firma ni numera el CFE, tampoco se almacena. Útil para hacer pruebas.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Identificación de terminal. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. |
| `version` | string | Indicador de versión de XSDs de DGI. Enviar vacío para usar la última versión. |

#### Salida

Ídem a la salida de `EnvioCFE`.

---

### ReservarNro

Permite **reservar un número** para un CFE. Se utiliza en conjunto con el servicio `ConfirmarCFE`.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Identificación de terminal. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `FVencimiento` | dateTime | Fecha de vencimiento del CAE. |
| `Nro` | int | Número del CFE. |
| `Serie` | string | Serie del CFE. |
| `Tipo` | int | Tipo del CFE. |
| `nauto` | decimal | Nº de autorización del CAE. |

---

### ConfirmarCFE

Permite ingresar en SICFE el CFE enviado en el servicio `ReservarNro`. Se comporta de manera similar al servicio `EnvioCFE`, realizando las validaciones de formato y aplicando la firma digital.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cliente` | string | *Opcional.* Identificación de terminal. |
| `cfexml` | string | CFE en formato XML de acuerdo a definición de DGI. **Debe estar numerado** con los datos devueltos por `ReservarNro`. |
| `referenciaERP` | string | Identificador de la transacción, permite reconocer envíos duplicados. |
| ~~`referenciaERP2`~~ | string | **Deprecado.** |
| ~~`erpPideValidacion`~~ | boolean | **Deprecado.** |
| `version` | string | Indicador de versión de XSDs de DGI. Enviar vacío para usar la última versión. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `EsReceptorElectronico` | boolean | Indica si el receptor es electrónico. |
| `IdCFE.Numero` | int | Número del CFE. |
| `IdCFE.Serie` | string | Serie del CFE. |
| `IdCFE.Tipo` | int | Tipo del CFE. |
| `IdCFE.observado` | int | Sólo CFC. Indica cuántas veces se ha observado. |
| `IdCFE.rucemisor` | string | RUC del emisor del CFE. |
| `ImagenQR` | string | Imagen en PNG del código QR en base64. |
| `LinkQR` | string | Enlace al sitio web de DGI con los datos del CFE. |
| `datosCAE.dnro` | int | Nº inicial del CAE. |
| `datosCAE.fvto` | datetime | Fecha de vencimiento del CAE. |
| `datosCAE.hnro` | int | Nº final del CAE. |
| `datosCAE.nauto` | decimal | Nº de autorización del CAE. |
| `hash` | string | Código hash del CFE. |
| `xml` | string | XML del CFE con firma digital y CAE aplicados. |

---

### AnularNumeracion

Permite **anular un rango de numeración (CAE)**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `p_tipo` | int | Tipo de CFE del CAE a anular. |
| `p_serie` | string | Serie del CAE a anular. |
| `p_desde` | int | Nº inicial del CAE a anular. |
| `p_hasta` | int | Nº final del CAE a anular. |
| `p_sucursal` | int | Sucursal desde la cual se está anulando el CAE. |
| `es_pre_numerado` | boolean | Utilizar `true` si se quiere anular una reserva de número hecha con `ReservarNro`. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### AnularRango (deprecado)

> **Servicio deprecado.** Utilizar `AnularNumeracion` en su lugar.

---

## Recepción

### ObtenerCFEsRecibidos

Obtiene todos los **CFEs recibidos** (emitidos por proveedores) para un tenant dado, pudiendo filtrar por un determinado rango de fechas y estado.

Los filtros son opcionales; en caso de no querer utilizarlos, se pueden enviar como `null`. Se recomienda utilizar el filtro de fecha para evitar traer una gran cantidad de CFEs.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `fecha_desde` | string | *Opcional.* Filtro de fecha desde. Formato `AAAA-MM-DD`. |
| `fecha_hasta` | string | *Opcional.* Filtro de fecha hasta. Formato `AAAA-MM-DD`. |
| `estado` | string | *Opcional.* Filtro de estado. Valores posibles: `"IN"`, `"IO"`, `"AE"`, `"BE"`. String vacío = sin filtro. |
| `rucEmisor` | string | *Opcional.* RUC del proveedor. |

#### Salida

`CFEsRecibidos` — Array de elementos `CFERecibidoDTO`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `Estado` | string | Estado del CFE. |
| `FechaEmision` | string | Fecha de emisión del CFE. |
| `Numero` | long | Número del CFE. |
| `RucEmisor` | string | RUC del proveedor. |
| `Serie` | string | Serie del CFE. |
| `Tipo` | int | Tipo del CFE. |
| `XML` | string | XML del CFE. |

---

### ObtenerCFEsRecibidosExtendido

Similar al servicio anterior, pero devuelve **información más detallada** de cada CFE.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `fecha_desde` | string | *Opcional.* Filtro de fecha desde. Formato `AAAA-MM-DD`. |
| `fecha_hasta` | string | *Opcional.* Filtro de fecha hasta. Formato `AAAA-MM-DD`. |
| `estado` | string | *Opcional.* Filtro de estado. Valores: `"IN"`, `"IO"`, `"AE"`, `"BE"`, `"BN"`, `"AM"`, `"BM"`. Se puede filtrar por más de un estado separándolos por coma (ej: `AE,BE,IO`). |
| `devolverXML` | boolean | Indica si se debe devolver el XML del CFE recibido. |
| `rucEmisor` | string | *Opcional.* RUC del proveedor. |
| `consideraCobranzas` | string | *Opcional.* `"1"` = solo cobranzas; `"2"` = omite cobranzas. Cualquier otro valor u omisión = sin filtro. Disponible a partir de la versión 2.41. |

#### Salida

`CFEsRecibidos` — Array de elementos `CFERecibidoExtendidoDTO`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `DesdeNroCAE` | int | Nº inicial del CAE. |
| `Estado` | string | Estado del CFE. |
| `FechaEmision` | string | Fecha de emisión. |
| `FechaProcesado` | dateTime | Fecha en la que SICFE recibió y procesó el CFE. |
| `FechaVencimiento` | string | Fecha de vencimiento. |
| `FechaVencimientoCAE` | string | Fecha de vencimiento del CAE. |
| `FormaPago` | short | Forma de pago (`1` = Contado; `2` = Crédito). |
| `HastaNroCAE` | int | Nº final del CAE. |
| `MntPagar` | decimal | Monto a pagar. |
| `MntTotCreditoFiscal` | decimal | Monto total de créditos fiscales. |
| `MntTotRetenido` | decimal | Monto total retenido. |
| `Moneda` | string | Moneda. |
| `NombreComercial` | string | Nombre comercial del proveedor. |
| `Numero` | long | Número del CFE. |
| `NumeroAutorizacionCAE` | long | Nº de autorización del CAE. |
| `RazonSocial` | string | Razón social del proveedor. |
| `RucEmisor` | string | RUC del proveedor. |
| `Serie` | string | Serie del CFE. |
| `Tipo` | int | Tipo del CFE. |
| `XML` | string | XML del CFE. |

---

### ConfirmarCFERecibido

**Marca un CFE recibido como confirmado.** La próxima vez que se utilice `ObtenerCFEsRecibidos` y/o `ObtenerCFEsRecibidosExtendido`, el CFE no se traerá en los resultados.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `tipo` | short | Tipo del CFE. |
| `serie` | string | Serie del CFE. |
| `numero` | long | Número del CFE. |
| `rucemisor` | string | RUC del proveedor. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### DesconfirmarCFERecibido

**Marca un CFE recibido como desconfirmado.** La próxima vez que se utilice `ObtenerCFEsRecibidos` y/o `ObtenerCFEsRecibidosExtendido`, el CFE **sí se traerá** en los resultados.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `tipo` | short | Tipo del CFE. |
| `serie` | string | Serie del CFE. |
| `numero` | long | Número del CFE. |
| `rucemisor` | string | RUC del proveedor. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### AceptarCFERecibido

**Marca un CFE recibido como aceptado.**

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `p_rucEmisor` | long | RUC del contribuyente que está haciendo la aceptación comercial. |
| `p_idCFE.Numero` | int | Número del CFE. |
| `p_idCFE.Serie` | string | Serie del CFE. |
| `p_idCFE.Tipo` | int | Tipo del CFE. |
| `p_idCFE.observado` | int | Indica cantidad de observados. Utilizar `1` para CFE que no sean Contingencia. |
| `p_idCFE.rucemisor` | string | RUC del proveedor. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### RechazarCFERecibido

**Marca un CFE recibido como rechazado.**

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `p_rucEmisor` | long | RUC del contribuyente que está haciendo el rechazo comercial. |
| `p_idCFE.Numero` | int | Número del CFE. |
| `p_idCFE.Serie` | string | Serie del CFE. |
| `p_idCFE.Tipo` | int | Tipo del CFE. |
| `p_idCFE.observado` | int | Indica cantidad de observados. Utilizar `1` para CFE que no sean Contingencia. |
| `p_idCFE.rucemisor` | string | RUC del proveedor. |
| `p_motivosRechazo.RechazoAcuseRecibo.Codigo` | string | Código de rechazo (ver tabla). |
| `p_motivosRechazo.RechazoAcuseRecibo.Descripcion` | string | Completar cuando se utiliza algún código entre `E27-E60`. |

**Códigos de rechazo disponibles:**

| Código | Descripción |
|---|---|
| `E02` | Tipo y Nº de CFE ya existe en los registros. |
| `E03` | Tipo y Nº de CFE no se corresponden con el CAE. |
| `E04` | Firma electrónica no es válida. |
| `E05` | No cumple validaciones de formato CFE. |
| `E07` | Fecha Firma de CFE no se corresponde con fecha CAE. |
| `E08` | No coinciden RUC emisor de CFE y Complemento fiscal. |
| `E09` | Tipo de CFE recibido no figura en la base de CFE certificados. |
| `E20` | Orden de compra vencida. |
| `E21` | Mercadería en mal estado. |
| `E22` | Proveedor inhabilitado por organismo de contralor. |
| `E23` | Contraprestación no recibida. |
| `E24` | Diferencia precios y/o descuentos. |
| `E25` | Factura con error cálculos. |
| `E26` | Diferencia con plazo. |
| `E27–E60` | Motivos personalizados. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### ObtenerEstadoDGIRecibido

Devuelve el **estadoDGI del CFE recibido** a filtrar.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de hasta 6 caracteres asignado por SICFE a la razón social. |
| `p_rucEmisor` | long | RUC del contribuyente que está haciendo el rechazo comercial. |
| `tipo` | int | Tipo del CFE. |
| `serie` | string | Serie del CFE. |
| `numero` | int | Número del CFE. |
| `rucEmisor` | string | RUC del proveedor. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Codigo` | int | Código de la operación. Cero en caso de éxito. |
| `Descripcion` | string | En caso de error, detalla el mismo. |
| `EstadoDGI` | string | Estado DGI (ver tabla). |

**Valores de EstadoDGI:**

| Código | Descripción |
|---|---|
| `AE` | Aceptado. |
| `BE` | Rechazado. |
| `SP` | Secreto Profesional. |
| `SD` | Solamente DGI. |
| `IN`, `IO` | Ingresado. |
| `PE`, *(vacío)* | Pendiente. |
| `SR` | Sin Respuesta. |

---

## Impresión

### ObtenerPDF

Permite obtener la **representación impresa de un CFE** (emitido o recibido) en formato PDF.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `p_idCFE.Numero` | int | Número del CFE. |
| `p_idCFE.Serie` | string | Serie del CFE. |
| `p_idCFE.Tipo` | int | Tipo del CFE. |
| `p_idCFE.observado` | int | Indica cantidad de observados. Utilizar `1` para CFE que no sean Contingencia. |
| `p_idCFE.rucemisor` | string | RUC del proveedor. |
| `p_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `template` | string | Nombre de la plantilla a utilizar. Dejar vacío para usar la plantilla por defecto. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Buffer` | base64Binary | Bytes del PDF codificados en una cadena base64. |

---

### ObtenerPDFUnico

Permite obtener la **representación impresa de varios CFEs** (emitidos y/o recibidos) en un único PDF.

> **Limitación:** máximo 50 CFEs por defecto.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `cfes` | List\<IdCFE\> | Lista con los CFEs para generar el PDF. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Buffer` | base64Binary | Bytes del PDF codificados en una cadena base64. |

---

### Reimprimir

Permite **imprimir un CFE** en una impresora física.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `p_ID.Numero` | int | Número del CFE. |
| `p_ID.Serie` | string | Serie del CFE. |
| `p_ID.Tipo` | int | Tipo del CFE. |
| `p_ID.observado` | int | Indica cantidad de observados. |
| `p_ID.rucemisor` | string | RUC del proveedor. |
| `impresora` | string | Nombre de la impresora como aparece en Windows, o nombre de red. |
| `nroImpresiones` | int | Cantidad de copias. |
| `template` | string | Nombre de la plantilla a utilizar. Dejar vacío para usar la plantilla por defecto. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### ObtenerRecursosImpresion

Permite obtener una **lista de las impresoras instaladas** en el sistema.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`ListRecursosImpresion` — Array de elementos `Impresora`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `Color` | boolean | Indica si la impresora es a color o no. |
| `Nombre` | string | Nombre de la impresora. |
| `Papel` | string | Tamaño de papel por defecto soportado por la impresora. |
| `PorDefecto` | boolean | Indica si es la impresora por defecto de Windows. |

---

### ObtenerTemplatesImpresion

Permite obtener una **lista de las plantillas de impresión** de CFEs.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`ListTemplatesImpresion` — Array de elementos `DatoTemplateImpresion`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `CodTemplate` | string | Código de la plantilla. |
| ~~`EstiloTemplate`~~ | int | **Deprecado.** |
| `IdPlantilla` | int | Identificación de la plantilla. |
| ~~`Template`~~ | base64Binary | **Deprecado.** |
| `TemplateNombre` | string | Nombre de la plantilla. |

---

### ObtenerPDFSinProcesar

Permite obtener el **PDF de un CFE sin antes haberlo enviado a SICFE**. Sirve a modo de Vista Previa de cómo quedaría el PDF.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `xml` | string | XML del CFE. |
| `p_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `tipoCFE` | int | Tipo del CFE. |
| `template` | string | Nombre de la plantilla a utilizar. Dejar vacío para usar la plantilla por defecto. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Buffer` | base64Binary | Bytes del PDF codificados en una cadena base64. |

---

## Otros Servicios

### ActualizarReceptoresNoElectronicos

Permite dar de alta o actualizar un **receptor no electrónico** (que recibe PDF en una casilla de correo).

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `p_nomusuario` | string | Nombre de usuario asignado al ERP. |
| `p_pass` | string | Contraseña del usuario asignado al ERP. |
| `p_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `p_datoReceptor.CodPais` | string | Código país ISO 3166-1 alfa-2 del receptor. |
| `p_datoReceptor.Doc` | string | Número del documento del receptor. |
| `p_datoReceptor.Email` | string | Correo del receptor. Varios separados por `,` (coma). |
| `p_datoReceptor.Nombre` | string | Nombre del receptor. |
| `p_datoReceptor.TipoDoc` | int | Tipo de documento: `0` = RUC, `1` = C.I, `2` = Pasaporte, `3` = DNI Ext., `4` = Otros. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### ObtenerReceptoresNoElectronicos

Obtiene los datos correspondientes a los **receptores no electrónicos** asociados a la empresa. En caso de ingresar `numerodoc` y `tipodoc`, obtiene los datos únicamente de dicho receptor.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `numerodoc` | string | *Opcional.* Número de documento del receptor NE. |
| `tipodoc` | string | *Opcional.* Tipo de documento del receptor NE. |

#### Salida

`ReceptoresNoElectronicos` — Lista de `DatosReceptoresNoElectronicos`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `Nombre` | string | Nombre del receptor. |
| `TipoDocumento` | short | `0` = RUC, `1` = C.I, `2` = Pasaporte, `3` = DNI Ext., `4` = Otros. |
| `NumeroDocumento` | string | Número de documento. |
| `EMails` | string | Todos los emails separados por `,` (coma). |
| `CodigoPais` | string | Código del país del receptor NE. |
| `FechaModificacion` | DateTime | Fecha en que fue modificado el receptor NE. |

---

### ConsolidarComprobantes

Permite obtener un **listado de CFEs para realizar conciliaciones** con el ERP.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `fechaInicio` | dateTime | Fecha inicio. Formato `AAAA-MM-DD`. |
| `fechaFin` | dateTime | Fecha fin. Formato `AAAA-MM-DD`. |

#### Salida

`Comprobantes` — Array de `DataCfe`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `EstadoDGI` | string | Estado del CFE en DGI. |
| `FechaEmision` | dateTime | Fecha de emisión del CFE. |
| `MontoTotal` | decimal | Monto total del CFE. |
| `Numero` | int | Número del CFE. |
| `ReferenciaErp` | string | Referencia del CFE (enviada por el ERP). |
| `Serie` | string | Serie del CFE. |
| `TipoCfe` | int | Tipo del CFE. |

---

### EsEmisorReceptorElectronico

Permite obtener si una empresa **es emisora/receptora electrónica** (está habilitada por DGI) y su casilla de intercambio.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `ruc` | string | RUC del emisor/receptor. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Electronico` | boolean | Indica si es electrónico o no. |
| `EmailIntercambio` | string | Casilla de correo de intercambio de CFEs. |
| `RazonSocial` | string | Razón social de la empresa. |

---

### ObtenerCAE

Permite obtener los **CAEs asociados a un tenant**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`CAEList` — Array de `DatoCAE`:

| Nombre | Tipo | Descripción |
|---|---|---|
| ~~`IdNumerador`~~ | int | **Deprecado.** |
| ~~`IdNumeradorClave`~~ | string | **Deprecado.** |
| `Tipo` | int | Tipo de CFE del CAE. |
| `cantusados` | int | Cantidad de números utilizados. |
| `dnro` | int | Número inicial del CAE. |
| ~~`estado`~~ | int | **Deprecado.** |
| `fanulacion` | dateTime | Fecha de anulación del CAE. |
| `femision` | dateTime | Fecha de emisión del CAE. |
| `fvto` | dateTime | Fecha de vencimiento del CAE. |
| `hnro` | int | Número final del CAE. |
| `nauto` | decimal | Número de autorización del CAE. |
| `serie` | string | Serie del CAE. |
| `sucursal` | int | Sucursal asignada al CAE. |
| `tauto` | string | Tipo de autorización del CAE. |
| `tenant` | string | Tenant del CAE. |
| `ultnro` | int | Último número utilizado del CAE. |
| ~~`xml`~~ | string | **Deprecado.** |

---

### ObtenerCFEPorID

Permite obtener **información de un CFE** dado su ID (tipo, serie y número).

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `tipo` | short | Tipo del CFE. |
| `serie` | string | Serie del CFE. |
| `numero` | long | Número del CFE. |
| `devolverImagenQR` | boolean | Determina si se devuelve el QR. |
| `devolverXML` | boolean | Si se debe devolver el XML del CFE (firmado y numerado). |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `docreceptor` | string | Número de documento del receptor. |
| `estadoEnvioReceptorNE` | string | Estado de envío al receptor no electrónico. |
| `estadoenvioDGI` | string | Estado de envío a DGI. |
| `estadoenvioreceptor` | string | Estado de envío al receptor electrónico. |
| `fechafirma` | dateTime | Fecha de firma del CFE. |
| `femision` | dateTime | Fecha de emisión del CFE. |
| `fyhrecibido` | dateTime | Fecha y hora de procesamiento del CFE. |
| `numero` | int | Número del CFE. |
| `observado` | int | Indica cantidad de observados. |
| `referenciaerp` | string | Referencia del CFE (enviada por el ERP). |
| `serie` | string | Serie del CFE. |
| `sucursal` | int | Sucursal de donde se emitió el CFE. |
| `tipo` | int | Tipo del CFE. |
| `imagenQR` | — | Imagen en PNG del código QR codificada en base64. |
| `linkQR` | — | Enlace al sitio web de DGI con los datos del CFE. |
| `xml` | string | XML del CFE. |

---

### ObtenerCFEPorReferencia

Permite obtener **información de un CFE** dada su referencia ERP.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `referenciaERP` | string | Referencia del CFE (enviada por el ERP). |
| `devolverImagenQR` | boolean | Determina si se devuelve el QR. |
| `devolverXML` | boolean | Si se debe devolver el XML del CFE (firmado y numerado). |

#### Salida

Ídem a la salida de `EnvioCFE`.

---

### ObtenerCFEPorReferenciaConRespuestaDeEnvio

Permite obtener información de un CFE dada su referencia ERP y **devuelve una respuesta como si fuera `EnvioCFE()`**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `referenciaERP` | string | Referencia del CFE (enviada por el ERP). |
| `devolverImagenQR` | boolean | Determina si se devuelve el QR. |
| `devolverXML` | boolean | Si se debe devolver el XML del CFE (firmado y numerado). |

#### Salida

Mismo esquema que `ObtenerCFEPorID`.

---

### ObtenerClientesElectronicos

Permite obtener un listado de los **emisores/receptores electrónicos que sean clientes** de la empresa.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`Clientes` — Array de `ClienteElectronico`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `Email` | string | Casilla de intercambio del cliente. |
| `RazonSocial` | string | Razón Social del cliente. |
| `Ruc` | string | RUC del cliente. |

---

### ObtenerDatosDeEmisorReceptor

Permite obtener los **datos de un emisor/receptor electrónico**, si existe.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `ruc` | string | RUC del emisor/receptor. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `EmailIntercambio` | string | Casilla de correo de intercambio de CFEs. |
| `FechaFin` | string | Fecha de fin de emisor/receptor electrónico. |
| `FechaFinTransicion` | string | Fecha de fin del período de transición (4 meses). |
| `FechaInicio` | string | Fecha de inicio como emisor/receptor electrónico. |
| `RUC` | string | RUC del emisor/receptor. |
| `RazonSocial` | string | Razón social de la empresa. |

---

### ObtenerInfoCertificados

Permite obtener un listado de los **certificados digitales asociados a la empresa**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`Certificados` — Array de `Certificado`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `DiasRestantes` | int | Días restantes de vigencia de certificado. |
| `Estado` | string | Estado: `Activo` / `Inactivo` / `Vencido`. |
| `VigenciaDesde` | dateTime | Fecha de vigencia inicial. |
| `VigenciaHasta` | dateTime | Fecha de vigencia final. |

---

### ObtenerProveedoresElectronicos

Permite obtener un listado de los **emisores/receptores electrónicos que sean proveedores** de la empresa.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

`Proveedores` — Array de `ProveedorElectronico`:

| Nombre | Tipo | Descripción |
|---|---|---|
| `Email` | string | Casilla de intercambio del proveedor. |
| `RazonSocial` | string | Razón Social del proveedor. |
| `Ruc` | string | RUC del proveedor. |

---

### ObtenerVersion

Permite obtener la **versión del sistema SICFE Middleware**.

#### Entrada

Sin parámetros de entrada.

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `VersionBD` | string | Versión de la base de datos. |
| `VersionSvc` | string | Versión de los servicios. |

---

### ObtenerXML_DGI

Permite obtener el **XML del CFE especificado**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `p_idCFE.Numero` | — | Número del CFE. |
| `p_idCFE.Serie` | — | Serie del CFE. |
| `p_idCFE.Tipo` | — | Tipo del CFE. |
| `p_idCFE.observado` | — | Indica cantidad de observados. Utilizar `1` para CFE que no sean Contingencia. |
| `p_idCFE.rucemisor` | — | RUC del emisor. |
| `p_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `xml` | string | XML del CFE. |

---

### PingSICFEEmisor

Permite realizar un **ping al servicio de SICFE Middleware**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |

#### Salida

| Nombre | Tipo | Descripción |
|---|---|---|
| `Testing` | boolean | Indica si es ambiente de testing o no. |

---

### AgregarCAE

Permite agregar **varios CAE** para un tenant específico.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `param_tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `ListaCAE` | List\<string\> | Lista de XML con datos necesarios para crear los CAE. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### ReenvioComprobante

Permite el **reenvío de un comprobante** en formato PDF y/o XML ya emitido o recibido a los emails ingresados.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `numero` | int | Número del CFE. |
| `serie` | string | Serie del CFE. |
| `tipoCfe` | string | Tipo del CFE. |
| `emails` | string | Emails a los que se enviará el comprobante. Separados por coma si son varios. |
| `enviarPDF` | boolean | `TRUE` para realizar el envío del PDF. |
| `enviarXml` | boolean | `TRUE` para realizar el envío del XML. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### ObtenerDatosRUCDGI

Busca datos de un RUC en **tres fuentes distintas** (padrón de emisores/receptores en facturación electrónica, consulta de certificado de vigencia anual/único, y datos registrales en DGI) y devuelve la información procesada.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `pass` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `ruc` | string | RUC del contribuyente que se desea consultar. |

#### Salida

> *La información y valores retornados pueden variar dado que los servicios consultados son propiedad de DGI.*

| Nombre | Tipo | Descripción |
|---|---|---|
| `Codigo` | int | Código del resultado. `0` = éxito. |
| `Descripcion` | string | Descripción del código de error. |
| `RUC` | string | RUC del contribuyente. |
| `Denominacion` | string | Razón Social declarada ante DGI. |
| `NombreFantasia` | string | Nombre de fantasía declarado ante DGI. |
| `FacturacionElectronica_EmailIntercambio` | string | Email de intercambio de CFEs. |
| `FacturacionElectronica_FechaInicio` | string | Fecha de inicio en facturación electrónica. |
| `FacturacionElectronica_FechaFin` | string | Fecha de fin en facturación electrónica. |
| `FacturacionElectronica_FechaFinTransicion` | string | Fecha de fin del período de transición. |
| `CertificadoVigenciaAnual_Estado` | string | Estado del certificado único. Ej: `"Certificado de Vigencia Anual Habilitado."` |
| `CertificadoVigenciaAnual_FechaEmision` | string | Fecha de emisión del certificado único. |
| `CertificadoVigenciaAnual_FechaVencimiento` | string | Fecha de vencimiento del certificado único. |
| `CertificadoVigenciaAnual_TipoContribuyente` | string | Tipo de contribuyente: `"CEDE"` / `"NOCEDE"`. |
| `TipoEntidad` | string | Código del tipo de entidad. |
| `DescripcionTipoEntidad` | string | Descripción del tipo de entidad. Ej: `"SOCIEDAD ANÓNIMA CON ACCIONES NOMINATIVAS"`. |
| `EstadoActividad` | string | Código de estado de actividad. |
| `FechaInicioActividad` | string | Fecha de inicio de actividades. |
| `TipoLocal_Dsc` | string | Descripción de la sucursal. |
| `Local_Sec_Nro` | string | Código de la sucursal principal. |
| `Calle` | string | Calle del contribuyente. |
| `NroPuerta` | string | Número de puerta. |
| `NroApto` | string | Número de apartamento. |
| `Localidad` | string | Localidad. |
| `Departamento` | string | Departamento. |
| `Dom_Pst_Cod` | string | Código postal. |
| `TelefonoFijo` | string | Teléfono fijo. |
| `TelefonoMovil` | string | Teléfono móvil. |
| `CorreoElectronico` | string | Correo electrónico. |
| `GiroCod1` – `GiroCod5` | string | Código de actividad 1–5. |
| `GiroNom1` – `GiroNom5` | string | Nombre de actividad 1–5. |
| `GiroFechaInicio1` – `GiroFechaInicio5` | string | Fecha de inicio de actividad 1–5. |

---

### AgregarLogo

Permite cargar un **logo a la empresa** para que se muestre en la representación impresa.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `nomusuario` | string | Nombre de usuario asignado al ERP. |
| `clave` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `logo` | byte[] | Logo en formato `.jpeg`, `.png` o `.jpg`. No puede superar los **512 KB**. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

### AgregarCertificado

Permite cargar un **certificado de la empresa**.

#### Entrada

| Nombre | Tipo | Descripción |
|---|---|---|
| `usuario` | string | Nombre de usuario asignado al ERP. |
| `claveUsuario` | string | Contraseña del usuario asignado al ERP. |
| `tenant` | string | Código de 6 caracteres asignado por SICFE a la razón social. |
| `claveCertificado` | string | Contraseña del certificado a agregar. |
| `certificado` | byte[] | Información del certificado. |

#### Salida

Devuelve `SICFERespuesta` (Código y Descripción).

---

## File System

La segunda forma de comunicación es a través del **intercambio de archivos**. La estructura de carpetas es la siguiente:

```
Root/
├── XML/
│   └── Procesados/
├── Result/
├── Pdf/
│   └── Imprimir/
│       └── Resultado/
└── Estados/
    └── Procesados/
```

### Funcionamiento

- **Envío de CFE:** Se inserta el archivo XML en la carpeta `XML`. El nombre del archivo debe corresponder al ID de la transacción y tener extensión `.xml`.

  > **Importante:** El identificador debe ser **único** para cada comprobante, ya que representa el ID de la transacción. Si se recibe un ID repetido, se devolverán los datos de la transacción anterior.

- **Respuesta:** El sistema procesa los archivos de la carpeta `XML`. La respuesta se escribe en la carpeta `Result`. En caso de éxito, el PDF se guarda en la carpeta `Pdf`. El archivo en `Result` tendrá el mismo nombre que el archivo procesado.

- **Consulta de estado:** Se puede configurar el sistema para obtener estados automáticamente, o a demanda creando un archivo XML en la carpeta `Estados` con la nomenclatura: `tipoCFE_serie_nro.xml`. La respuesta se escribe en la subcarpeta `Procesados`.

### Campos de respuesta de estado (File System)

| Campo | Código | Descripción |
|---|---|---|
| `Estado` | `"AE"` | CFE aceptado por DGI. |
| | `"BE"` | CFE rechazado por DGI. |
| | `"CE"` | CFE observado por DGI. |
| | `"PE"` | CFE pendiente de envío a DGI. |
| | `"EN"` | CFE enviado a DGI. |
| | `"RE"` | CFE rechazado por sobre por DGI. |
| | `"NA"` | eTicket < 10.000 UI, no aplica envío a DGI. |
| | `""` | El CFE aún no ha sido procesado por SICFE para su envío. |
| `CodRechazo` | `"E01"` | Tipo y Nº de CFE ya fue reportado como anulado. |
| | `"E02"` | Tipo y Nº de CFE ya existe en los registros. |
| | `"E03"` | Tipo y Nº de CFE no se corresponden con el CAE. |
| | `"E04"` | Firma electrónica no es válida. |
| | `"E05"` | No cumple validaciones de formato CFE. |
| | `"E07"` | Fecha Firma de CFE no se corresponde con fecha CAE. |
| | `"E08"` | No coinciden RUC emisor de CFE y Complemento fiscal. |
| `MotRechazo` | — | Descripción del código de rechazo. |

> **Importante:** `CodRechazo` y `MotRechazo` se cargan cuando el valor de `Estado` es `"BE"`.

### Impresión via File System

Para imprimir un documento, se crea un archivo `.txt` en la carpeta `Imprimir` conteniendo el nombre de la impresora. El nombre del archivo debe cumplir con el formato:

```
tipoCFE-serie-nro-template.txt
```

En la subcarpeta `Resultado` se crea un archivo con el resultado de la operación, incluyendo código y descripción.

---

## Tiempos de envío

Resultados de tiempos al realizar el envío de 10.000 CFEs a la API de Sicfe. Escenario: Sicfe v2.41.595, servidor en la nube de Sicfe en Brasil, e-Ticket con 4 líneas de detalle, todas las validaciones aplicadas, numeración y firma por Sicfe.

| Prueba | Muestras | Promedio (ms) | 90% (ms) | 99% (ms) | Min (ms) | Max (ms) | Error (%) | Throughput (cfes/min) | Tiempo total (h) |
|---|---|---|---|---|---|---|---|---|---|
| Secuencial | 10.000 | 1.080 | 1.121 | 1.379 | 795 | 13.511 | 0,00 | 54,0 | 3:20:00 |
| Paralelo (5 hilos*) | 10.000 | 1.764 | 2.713 | 10.178 | 754 | 23.393 | 0,09 | 168,0 | 1:01:00 |

> \* **No es recomendable realizar más de 5 envíos en paralelo** ya que aumenta el porcentaje de error por sobrecarga del servicio.

---

## Formato CFE

Para confeccionar el XML se debe seguir la especificación de formato de la DGI:

🔗 [SICFE — Formato CFE XML](https://docs.google.com/spreadsheets/d/e/2PACX-1vRenMjYc3QarQlDkeGeSPNMPJAzQ1nNIVgGS3dpFWFzIima5QEfg75t1-Vl7mC7FmrAnLEx4bSIaYB3/pubhtml)

---

## Ejemplos con SOAP UI

Para configurar SOAP UI y utilizar los servicios de SICFE Middleware:

1. Descargar SOAP UI desde [soapui.org](https://www.soapui.org).
2. Abrir la herramienta y agregar un nuevo proyecto WSDL (`File > New SOAP Project`).
3. Pegar la URL del servicio proporcionada por SICFE (ambiente de test o producción).
4. Los servicios disponibles aparecen en el panel lateral izquierdo.
5. Clic derecho sobre el servicio → **New request**.
6. Se abre una ventana con el SOAP Envelope. Cargar los parámetros necesarios.
7. Los resultados de las invocaciones se muestran en el panel derecho.

---

## Emisión — Ejemplos

### EnvioCFE

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:EnvioCFE>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml]]></tem:cfexml>
      <tem:referenciaERP>123456789</tem:referenciaERP>
      <tem:referenciaERP2></tem:referenciaERP2>
      <tem:devolverQR>1</tem:devolverQR>
      <tem:sizeQR>30</tem:sizeQR>
      <tem:imprime>0</tem:imprime>
      <tem:recurso>0</tem:recurso>
      <tem:template></tem:template>
      <tem:devolverXML>1</tem:devolverXML>
      <tem:erpPideValidacion>0</tem:erpPideValidacion>
      <tem:version></tem:version>
    </tem:EnvioCFE>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <EnvioCFEResponse xmlns="http://tempuri.org/">
      <EnvioCFEResult xmlns:a="http://schemas.datacontract.org/2004/07/SICFEContract"
                      xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
        <a:Codigo>0</a:Codigo>
        <a:Descripcion i:nil="true"/>
        <a:EsReceptorElectronico>false</a:EsReceptorElectronico>
        <a:IdCFE>
          <a:Numero>4</a:Numero>
          <a:Serie>A</a:Serie>
          <a:Tipo>101</a:Tipo>
          <a:observado>1</a:observado>
          <a:rucemisor>217042750013</a:rucemisor>
        </a:IdCFE>
        <a:ImagenQR>iVBORw0KGgoAAAANS...</a:ImagenQR>
        <a:LinkQR>https://www.efactura.dgi.gub.uy/consultaQR/cfe?217042750013,101,A,4,122.00,05/09/2018,e788pvnkq2ardCGvggQnNKs9L0U%3d</a:LinkQR>
        <a:datosCAE>
          <a:dnro>1</a:dnro>
          <a:fvto>2019-12-31T00:00:00</a:fvto>
          <a:hnro>10000</a:hnro>
          <a:nauto>90180001010</a:nauto>
        </a:datosCAE>
        <a:hash>e788pvnkq2ardCGvggQnNKs9L0U=</a:hash>
        <a:xml><![CDATA[xml-firmado-numerado]]></a:xml>
      </EnvioCFEResult>
    </EnvioCFEResponse>
  </s:Body>
</s:Envelope>
```

---

### EnvioCFESinFirmar

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:EnvioCFESinFirmar>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml-firmado-numerado]]></tem:cfexml>
      <tem:version></tem:version>
      <tem:refErp>12345678</tem:refErp>
    </tem:EnvioCFESinFirmar>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:** Ídem EnvioCFE.

---

### EnvioCFEConReceptor

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:EnvioCFEConReceptor>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml]]></tem:cfexml>
      <tem:referenciaERP>1234567890</tem:referenciaERP>
      <tem:devolverQR>1</tem:devolverQR>
      <tem:sizeQR>30</tem:sizeQR>
      <tem:imprime>0</tem:imprime>
      <tem:impresora></tem:impresora>
      <tem:template></tem:template>
      <tem:versionXSD></tem:versionXSD>
      <tem:correo>nombre@dominio.com</tem:correo>
      <tem:fechaEnvioCorreo>2019-12-31</tem:fechaEnvioCorreo>
    </tem:EnvioCFEConReceptor>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:** Ídem EnvioCFE.

---

### ObtenerEstadoCFE

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/"
    xmlns:sic="http://schemas.datacontract.org/2004/07/SICFEContract">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ObtenerEstadoCFE>
      <tem:pUsuario>erp</tem:pUsuario>
      <tem:pClave>erp</tem:pClave>
      <tem:pTenant>sicfe</tem:pTenant>
      <tem:pIDCFE>
        <sic:Numero>3</sic:Numero>
        <sic:Serie>A</sic:Serie>
        <sic:Tipo>101</sic:Tipo>
        <sic:observado>1</sic:observado>
        <sic:rucemisor>217042750013</sic:rucemisor>
      </tem:pIDCFE>
    </tem:ObtenerEstadoCFE>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ObtenerEstadoCFEResponse xmlns="http://tempuri.org/">
      <ObtenerEstadoCFEResult xmlns:a="http://schemas.datacontract.org/2004/07/SICFEContract"
                               xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
        <a:Codigo>0</a:Codigo>
        <a:Descripcion>Operación exitosa</a:Descripcion>
        <a:CodRechazo/>
        <a:Estado>NA</a:Estado>
        <a:MotRechazo/>
      </ObtenerEstadoCFEResult>
    </ObtenerEstadoCFEResponse>
  </s:Body>
</s:Envelope>
```

---

### ValidarEnvioCFE

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ValidarEnvioCFE>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml]]></tem:cfexml>
      <tem:version></tem:version>
    </tem:ValidarEnvioCFE>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ValidarEnvioCFEResponse xmlns="http://tempuri.org/">
      <ValidarEnvioCFEResult ...>
        <a:Codigo>0</a:Codigo>
        <a:Descripcion>Validación exitosa</a:Descripcion>
        <a:EsReceptorElectronico>false</a:EsReceptorElectronico>
        <a:IdCFE i:nil="true"/>
        <a:ImagenQR i:nil="true"/>
        <a:LinkQR i:nil="true"/>
        <a:datosCAE i:nil="true"/>
        <a:hash i:nil="true"/>
        <a:xml i:nil="true"/>
      </ValidarEnvioCFEResult>
    </ValidarEnvioCFEResponse>
  </s:Body>
</s:Envelope>
```

---

### ReservarNro

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ReservarNro>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml]]></tem:cfexml>
    </tem:ReservarNro>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ReservarNroResponse xmlns="http://tempuri.org/">
      <ReservarNroResult ...>
        <a:Codigo>0</a:Codigo>
        <a:Descripcion i:nil="true"/>
        <a:FVencimiento>2019-12-31T00:00:00</a:FVencimiento>
        <a:Nro>5</a:Nro>
        <a:Serie>A</a:Serie>
        <a:Tipo>101</a:Tipo>
        <a:nauto>90180001010</a:nauto>
      </ReservarNroResult>
    </ReservarNroResponse>
  </s:Body>
</s:Envelope>
```

---

### ConfirmarCFE

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ConfirmarCFE>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:cliente></tem:cliente>
      <tem:cfexml><![CDATA[xml-numerado]]></tem:cfexml>
      <tem:referenciaERP>confirmar_reservado</tem:referenciaERP>
      <tem:referenciaERP2></tem:referenciaERP2>
      <tem:erpPideValidacion>0</tem:erpPideValidacion>
      <tem:version></tem:version>
    </tem:ConfirmarCFE>
  </soapenv:Body>
</soapenv:Envelope>
```

---

### AnularNumeracion

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:AnularNumeracion>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:pass>erp</tem:pass>
      <tem:param_tenant>sicfe</tem:param_tenant>
      <tem:p_tipo>101</tem:p_tipo>
      <tem:p_serie>A</tem:p_serie>
      <tem:p_desde>1</tem:p_desde>
      <tem:p_hasta>2</tem:p_hasta>
      <tem:p_sucursal>2</tem:p_sucursal>
      <tem:es_pre_numerado>false</tem:es_pre_numerado>
    </tem:AnularNumeracion>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<a:Codigo>0</a:Codigo>
<a:Descripcion>Anulación exitosa</a:Descripcion>
```

---

## Recepción — Ejemplos

### ObtenerCFEsRecibidos

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ObtenerCFEsRecibidos>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:param_tenant>sicfe</tem:param_tenant>
      <tem:fecha_desde>2018-01-01</tem:fecha_desde>
      <tem:fecha_hasta>2018-01-31</tem:fecha_hasta>
      <tem:estado></tem:estado>
      <tem:rucEmisor></tem:rucEmisor>
    </tem:ObtenerCFEsRecibidos>
  </soapenv:Body>
</soapenv:Envelope>
```

---

### AceptarCFERecibido

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/"
    xmlns:sic="http://schemas.datacontract.org/2004/07/SICFEContract">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:AceptarCFERecibido>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:param_tenant>sicfe</tem:param_tenant>
      <tem:p_rucEmisor>217042750013</tem:p_rucEmisor>
      <tem:p_idCFE>
        <sic:Numero>18300</sic:Numero>
        <sic:Serie>A</sic:Serie>
        <sic:Tipo>111</sic:Tipo>
        <sic:observado>1</sic:observado>
        <sic:rucemisor>170176810010</sic:rucemisor>
      </tem:p_idCFE>
    </tem:AceptarCFERecibido>
  </soapenv:Body>
</soapenv:Envelope>
```

---

### RechazarCFERecibido

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/"
    xmlns:sic="http://schemas.datacontract.org/2004/07/SICFEContract">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:RechazarCFERecibido>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:param_tenant>sicfe</tem:param_tenant>
      <tem:p_rucEmisor>217042750013</tem:p_rucEmisor>
      <tem:p_idCFE>
        <sic:Numero>1022755</sic:Numero>
        <sic:Serie>A</sic:Serie>
        <sic:Tipo>111</sic:Tipo>
        <sic:observado>1</sic:observado>
        <sic:rucemisor>170096440012</sic:rucemisor>
      </tem:p_idCFE>
      <tem:p_motivosRechazo>
        <sic:RechazoAcuseRecibo>
          <sic:Codigo>E05</sic:Codigo>
          <sic:Descripcion>No cumple validaciones de Formato comprobantes</sic:Descripcion>
        </sic:RechazoAcuseRecibo>
      </tem:p_motivosRechazo>
    </tem:RechazarCFERecibido>
  </soapenv:Body>
</soapenv:Envelope>
```

---

## Impresión — Ejemplos

### ObtenerPDF

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/"
    xmlns:sic="http://schemas.datacontract.org/2004/07/SICFEContract">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ObtenerPDF>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:p_idCFE>
        <sic:Numero>4</sic:Numero>
        <sic:Serie>A</sic:Serie>
        <sic:Tipo>101</sic:Tipo>
        <sic:observado>1</sic:observado>
        <sic:rucemisor>217042750013</sic:rucemisor>
      </tem:p_idCFE>
      <tem:p_tenant>sicfe</tem:p_tenant>
      <tem:template></tem:template>
    </tem:ObtenerPDF>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<a:Codigo>0</a:Codigo>
<a:Buffer>JVBERi0xLjMNCjEgMCBvYmoNClsvUERGIC9UZXh0...==</a:Buffer>
```

---

### ObtenerPDFSinProcesar

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ObtenerPDFSinProcesar>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:xml><![CDATA[xml]]></tem:xml>
      <tem:p_tenant>sicfe</tem:p_tenant>
      <tem:tipoCFE>101</tem:tipoCFE>
      <tem:template></tem:template>
    </tem:ObtenerPDFSinProcesar>
  </soapenv:Body>
</soapenv:Envelope>
```

---

## Otros — Ejemplos

### EsEmisorReceptorElectronico

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:EsEmisorReceptorElectronico>
      <tem:ruc>217042750013</tem:ruc>
    </tem:EsEmisorReceptorElectronico>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<a:Codigo>0</a:Codigo>
<a:Electronico>true</a:Electronico>
<a:EmailIntercambio>cfe@sicfe.uy</a:EmailIntercambio>
<a:RazonSocial>SICFE SA</a:RazonSocial>
```

---

### PingSICFEEmisor

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:PingSICFEEmisor>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:clave>erp</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
    </tem:PingSICFEEmisor>
  </soapenv:Body>
</soapenv:Envelope>
```

**Response:**

```xml
<a:Codigo>0</a:Codigo>
<a:Descripcion>Exito</a:Descripcion>
<a:Testing>false</a:Testing>
```

---

### ObtenerDatosRUCDGI

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ObtenerDatosRUCDGI>
      <tem:usuario>sicfe</tem:usuario>
      <tem:clave>xxxxxx</tem:clave>
      <tem:tenant>sicfe</tem:tenant>
      <tem:ruc>217042750013</tem:ruc>
    </tem:ObtenerDatosRUCDGI>
  </soapenv:Body>
</soapenv:Envelope>
```

---

### ReenvioComprobante

**Request:**

```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:tem="http://tempuri.org/">
  <soapenv:Header/>
  <soapenv:Body>
    <tem:ReenvioComprobante>
      <tem:nomusuario>erp</tem:nomusuario>
      <tem:pass>erp</tem:pass>
      <tem:tenant>sicfe</tem:tenant>
      <tem:numero>1</tem:numero>
      <tem:serie>A</tem:serie>
      <tem:tipoCfe>101</tem:tipoCfe>
      <tem:emails>cfe@sicfe.uy</tem:emails>
      <tem:enviarPDF>1</tem:enviarPDF>
      <tem:enviarXml>0</tem:enviarXml>
    </tem:ReenvioComprobante>
  </soapenv:Body>
</soapenv:Envelope>
```

---

## Otros recursos

### Links útiles

| Recurso | URL |
|---|---|
| DGI — Sitio principal e-Factura | https://www.efactura.dgi.gub.uy/ |
| DGI — Documentos funcionales y técnicos | https://www.efactura.dgi.gub.uy/principal/ampliacion_de_contenido/DocumentosDeInteres1?es |
| DGI — Preguntas frecuentes | https://www.efactura.dgi.gub.uy/principal/Preguntas_Frecuentes?es |
| SICFE — Formato CFE XML | https://docs.google.com/spreadsheets/d/e/2PACX-1vRenMjYc3QarQlDkeGeSPNMPJAzQ1nNIVgGS3dpFWFzIima5QEfg75t1-Vl7mC7FmrAnLEx4bSIaYB3/pubhtml |

---

### Caracteres a escapar en XML

Los caracteres que se muestran a continuación deberán ser escapados (cuando están dentro de los elementos) para que los XML tengan un formato válido.

| Caracter | Caracter escapado |
|---|---|
| `&` | `&amp;` |
| `>` | `&gt;` |
| `<` | `&lt;` |
| `"` | `&quot;` |
| `'` | `&apos;` |

**Ejemplo INCORRECTO:**

```xml
<nsAd:RznSoc>RAZON & SOCIAL</nsAd:RznSoc>
<nsAd:NomComercial>Nombre < de la empresa</nsAd:NomComercial>
<nsAd:DomFiscal>DIRE " CCION</nsAd:DomFiscal>
```

**Ejemplo CORRECTO:**

```xml
<RznSoc>RAZON &amp; SOCIAL</RznSoc>
<NomComercial>Nombre &lt; de la empresa</NomComercial>
<DomFiscal>DIRE &quot; CCION</DomFiscal>
```

---

### Formato HTML en Adenda

Los tags a utilizar en el texto de la adenda serán los básicos de HTML. Hay que tener en cuenta que los mismos **deberán ser escapados** para poder ingresarlos en el XML (por los caracteres `>` y `<`).

Los tags soportados por la impresión de SICFE son:

| Tag | Uso |
|---|---|
| `<br>` | Salto de línea. |
| `<a>` | Insertar enlaces. |
| `<h1>` – `<h6>` | Encabezados. |
| `<p>` | Párrafos. |
| `<span>` | Dar color a un texto. |
| `<ol>` | Listas numeradas. |
| `<ul>` | Listas sin numerar (viñetas). |
| `<li>` | Elementos de lista. |
| `<b>` | Negrita. |
| `<i>` | Cursiva. |
| `<u>` | Subrayado. |

**Ejemplo HTML:**

```html
<A HREF="http://www.google.com">Enlace HTML</A>
<h1>Esto es un encabezado 1</h1>
<p>Prueba de <span style="color:blue">color</span>.</p>
<ol>
  <li>Cafe</li>
  <li>Te</li>
  <li>Leche</li>
</ol>
<p>Texto normal y <b>texto en negrita</b>.</p>
<p>Soy un texto en <i>cursiva</i>.</p>
<p>Soy un texto <u>subrayado</u>.</p>
```

**Ejemplo XML (escapado para incluir en CFE):**

```xml
&lt;A HREF=&quot;http://www.google.com&quot;&gt;Enlace HTML&lt;/A&gt;
&lt;h1&gt;Esto es un encabezado 1&lt;/h1&gt;
&lt;p&gt;Prueba de &lt;span style=&quot;color:blue&quot;&gt;color&lt;/span&gt;.&lt;/p&gt;
&lt;ol&gt;
&lt;li&gt;Cafe&lt;/li&gt;
&lt;li&gt;Te&lt;/li&gt;
&lt;li&gt;Leche&lt;/li&gt;
&lt;/ol&gt;
&lt;p&gt;Texto normal y &lt;b&gt;texto en negrita&lt;/b&gt;.&lt;/p&gt;
&lt;p&gt;Soy un texto en &lt;i&gt;cursiva&lt;/i&gt;.&lt;/p&gt;
&lt;p&gt;Soy un texto &lt;u&gt;subrayado&lt;/u&gt;.&lt;/p&gt;
```

---

*Fin del documento — © 2025 SICFE SA. Todos los derechos reservados.*
