Provides assets packaging and compression.

## Installation

### Add Tecbot\AssetPackagerBundle to your src/Bundle dir

    git submodule add git://github.com/tecbot/AssetPackagerBundle.git src/Bundle/Tecbot/AssetPackagerBundle
    
### Add TecbotAssetPackagerBundle to your application Kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            //..
            new Bundle\Tecbot\AssetPackagerBundle\TecbotAssetPackagerBundle(),
            //..
        );
    }
    
### Update your config

#### Add routing

    # app/config/routing.yml
    _assetpackager:
        resource: Tecbot/AssetPackagerBundle/Resources/config/routing.yml
        prefix:   /_ap

#### Add packages

    # app/config/config.yml
    assetpackager.config:
        js: # Javascript Config
            packages: # Javascript Packages
                core: # Package Name
                    - /js/underscore.js
                    - /js/backbone.js
                    - /js/main.js
        css: # Stylesheet Config
            packages: # Stylesheet Packages
                core: # Package Name
                    - /css/reset.css
                    - /css/grid.css
                    - /css/style.css

#### Advanced config
    
    # app/config/config.yml
    assetpackager.config:
        assets_path: String # Default to %kernel.root_dir%/../web
        cache_path: String # Default to %kernel.cache_dir%/assetpackager
        compress_assets: Boolean # Defaults to true. When false, JavaScript and CSS packages will be left uncompressed. Disabling compression is only recommended if you're packaging assets in development
        package_assets: Boolean # Defaults to true, packaging and caching assets
        js: # Javascript Config
            compressor: jsmin, packer, yui, closure # Defaults to jsmin
            options: ~ # compressor options. See compressor section
            packages: # Javascript Packages
                core: # Package Name
                    - /js/underscore.js
                    - /js/backbone.js
                    - /js/main.js
        css: # Stylesheet Config
            compressor: cssmin, yui # Defaults to cssmin
            options: ~ # compressor options. See compressor section
            packages: # Stylesheet Packages
                core: # Package Name
                    - /css/reset.css
                    - /css/grid.css
                    - /css/style.css

## Use

To use AssetPackagerBundle, call the classic method to add a stylesheet or javascript.

### Twig

    {% javascript 'core' %} # Package
    {% javascript 'js/main.js' %}
    {% stylesheet 'core' %} # Package
    {% stylesheet 'css/form.css' %}
    
    {% javascripts %}
    {% stylesheets %}

### PHP

    <?php $view['javascripts']->add('core') ?> # Package
    <?php $view['javascripts']->add('js/main.js') ?>
    <?php $view['stylesheets']->add('core') ?> # Package
    <?php $view['stylesheets']->add('css/form.css' ?>
    
    <?php echo $view['javascripts'] ?>
    <?php echo $view['stylesheets'] ?>

## Compressors Options

### Javascript compressors

#### JSMin

No option available

#### JavascriptPacker
    options:
        encoding:       None, Numeric, Normal, High ASCII # Defaults to Normal
        fast_decode:    Boolean # Defaults to true
        special_chars:  Boolean # Defaults to false

#### YUICompressor

    options:
        charset:                String # Defaults to utf-8
        line_break:             Number # Defaults to 5000  
        munge:                  Boolean # Defaults to true
        optimize:               Boolean # Defaults to true
        preserve_semicolons:    Boolean # Defaults to false
        path:                   String # Path of the yuicompressor.jar

#### Google Closure Compiler

    options:
        compilation_level:  WHITESPACE_ONLY, SIMPLE_OPTIMIZATIONS, ADVANCED_OPTIMIZATIONS # Defaults to OPTIMIZATIONS
        path:               String # Path of the closure-compiler.jar

### Stylesheet compressors

#### CSSMin

    options:
        remove-empty-blocks:     Boolean # Defaults to true
        remove-empty-rulesets:   Boolean # Defaults to true
        remove-last-semicolons:  Boolean # Defaults to true
        convert-css3-properties: Boolean # Defaults to false
        convert-color-values:    Boolean # Defaults to false
        compress-color-values:   Boolean # Defaults to false
        compress-unit-values:    Boolean # Defaults to false
        emulate-css3-variables:  Boolean # Defaults to true

#### YUICompressor

    options:
        charset:                String # Defaults to utf-8
        line_break:             Number # Defaults to 0
        path:                   String # Path of the yuicompressor.jar

## Command lines

### Clear the generated cache files

    console assetpackager:clear-cache

### Compress all packages

    console assetpackager:compress-packages
