var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/admin', './assets/js/admin.js')
    .addEntry('js/validator', './assets/js/validator.js')
    .addEntry('js/psmf', './assets/js/psmf.js')
    .addEntry('js/psmfIndex', './assets/js/psmfIndex.js')
    .addEntry('js/importer', './assets/js/importer.js')
    .addEntry('js/client', './assets/js/client.js')
    .addEntry('js/datatables', './assets/js/datatables.js')
    .addEntry('js/correspondance', './assets/js/correspondance.js')
    .addEntry('js/globale', './assets/js/globale.js')
    .addEntry('js/locale', './assets/js/locale.js')
    .addEntry('js/lazy', './assets/js/lazy.js')
    .addEntry('js/localeShow', './assets/js/localeShow.js')
    .addEntry('js/shepherd', './assets/js/shepherd.js')
    .addEntry('js/preloader', './assets/js/preloader.js')
    .addEntry('js/section', './assets/js/section.js')
    .addEntry('js/variable', './assets/js/variable.js')
    .addEntry('js/variableEdit', './assets/js/variableEdit.js')    
    .addEntry('js/user', './assets/js/user.js')
    .addEntry('js/userEdit', './assets/js/userEdit.js')
    .addEntry('js/login', './assets/js/login.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])
    .addStyleEntry('css/login', ['./assets/scss/login.scss'])
    .addStyleEntry('css/V2', ['./assets/scss/V2.scss'])
    .addStyleEntry('css/login-V2', ['./assets/scss/login-V2.scss'])
;

module.exports = Encore.getWebpackConfig();
