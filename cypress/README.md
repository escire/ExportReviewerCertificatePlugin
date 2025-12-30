# Cypress Testing Suite - Export Reviewer Certificate Plugin

Este directorio contiene los tests de integración E2E (end-to-end) para el plugin Export Reviewer Certificate usando Cypress.

## 📋 Prerequisitos

- Node.js >= 14.0.0
- npm >= 8.0.0
- OJS instalado y configurado
- Plugin Export Reviewer Certificate instalado

## 🚀 Instalación

```bash
npm install
```

## ⚙️ Configuración

1. Copia el archivo `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edita `.env` con tus credenciales y configuración:
   ```env
   CYPRESS_BASE_URL=https://tu-servidor.com
   CYPRESS_ADMIN_USER=tu_usuario_admin
   CYPRESS_ADMIN_PASSWORD=tu_password
   CYPRESS_REVIEWER_USER=usuario_revisor
   CYPRESS_REVIEWER_PASSWORD=password_revisor
   ```

## 🧪 Ejecución de Tests

### Modo interactivo (con interfaz gráfica):
```bash
npm run cypress:open
```

### Modo headless (sin interfaz):
```bash
npm run cypress:run
```

### Ejecutar en navegador específico:
```bash
npm run cypress:run:chrome
npm run cypress:run:firefox
```

### Ejecutar test específico:
```bash
npx cypress run --spec "cypress/e2e/exportReviewerCertificate/enablePlugin.cy.js"
```

## 📁 Estructura de Tests

```
cypress/
├── e2e/
│   └── exportReviewerCertificate/
│       ├── enablePlugin.cy.js          # Test de habilitación del plugin
│       ├── pluginManagement.cy.js      # Test de configuración del plugin
│       └── certificateDownload.cy.js   # Test de descarga de certificados
├── fixtures/
│   └── certificateFiles/               # Archivos de prueba (imágenes)
│       ├── watermark.png
│       ├── header.png
│       └── signature.jpg
└── support/
    ├── commands.js                     # Comandos personalizados de Cypress
    └── e2e.js                          # Configuración de soporte
```

## 📝 Tests Disponibles

### 1. **enablePlugin.cy.js**
- Verifica que el plugin puede ser habilitado correctamente
- Valida el estado de habilitación

### 2. **pluginManagement.cy.js**
- Configura todas las opciones del plugin
- Sube imágenes (marca de agua, cabecera, firma)
- Configura textos del certificado
- Verifica persistencia de configuración

### 3. **certificateDownload.cy.js**
- Simula descarga de certificado por un revisor
- Verifica que no se puede descargar dos veces
- Valida parámetros de la solicitud de descarga

## 🛠️ Comandos Personalizados

Los siguientes comandos personalizados están disponibles en los tests:

### `cy.login(user, password)`
Inicia sesión en OJS.

### `cy.navigateToPluginSettings(journalPath)`
Navega a la página de configuración de plugins.

### `cy.enablePlugin(pluginId)`
Habilita un plugin específico.

### `cy.uploadCertificateImage(fieldId, fileName, altText)`
Sube una imagen para el certificado.

### `cy.fillCertificateField(fieldId, text, locale)`
Llena un campo de texto localizado.

### `cy.fillCertificateTextField(fieldId, text)`
Llena un campo de texto simple.

### `cy.savePluginSettings()`
Guarda la configuración del plugin.

### `cy.openPluginSettingsModal(journalPath)`
Abre el modal de configuración del plugin.

## 🐛 Debugging

### Ver tests en modo interactivo:
```bash
npm run cypress:open
```

### Generar screenshots y videos:
Los screenshots se guardan automáticamente en `cypress/screenshots/` cuando hay fallos.
Los videos se guardan en `cypress/videos/` después de cada ejecución.

### Ver logs de Cypress:
Los comandos personalizados incluyen `cy.log()` para facilitar el debugging.

## 📊 Resultados

Después de ejecutar los tests en modo headless, puedes ver:
- **Videos**: `cypress/videos/`
- **Screenshots**: `cypress/screenshots/`
- **Reporte en consola**: Directamente en la terminal

## 🔧 Troubleshooting

### Error: "baseUrl is not configured"
Asegúrate de tener el archivo `.env` configurado o `cypress.config.js` con la URL correcta.

### Error: "Element not found"
Los selectores pueden variar según la versión de OJS. Revisa y ajusta los selectores en los tests.

### Tests lentos
Ajusta los timeouts en `cypress.config.js`:
```javascript
defaultCommandTimeout: 10000,
pageLoadTimeout: 60000,
```

## 📚 Recursos

- [Documentación de Cypress](https://docs.cypress.io)
- [Mejores prácticas de Cypress](https://docs.cypress.io/guides/references/best-practices)
- [OJS Documentation](https://docs.pkp.sfu.ca/dev/documentation/en/)

## 🤝 Contribuir

Para agregar nuevos tests:

1. Crea un nuevo archivo en `cypress/e2e/exportReviewerCertificate/`
2. Usa los comandos personalizados disponibles
3. Sigue el patrón de estructura existente
4. Documenta el propósito del test

## 📄 Licencia

Este código sigue la misma licencia del plugin Export Reviewer Certificate (GNU GPL v3).
