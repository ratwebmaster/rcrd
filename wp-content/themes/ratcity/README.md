# Zero
## WordPress Starter Theme

Zero is a WordPress starter theme that uses the [Twig](http://twig.sensiolabs.org) template engine.

## Setup

1. Copy contents of repository into a new theme folder with a unique name within the `wp-content/themes` directory of your WordPress install.
2. Edit your theme's details in `style.css`.

## Running Gulp Tasks

The CSS of the theme is compiled from a set of SASS files within the `css/scss` directory while the Javascript files in the `js` directory are minified. Both of these operations can be done via Gulp using the included `gulpfile.js` file.

In your theme's directory from terminal, install all the required node modules:

```
$ npm install
```

To run the Gulp watch task for compiling assets for development:

```
$ gulp watch:dev
```

To run the Gulp watch task for compiling assets for production:

```
$ gulp watch:prod
```

The difference between the development and production tasks is the inclusion of the [Bless](http://blesscss.com) plugin to split up large CSS files. This plugin requires additional time to run and will break the sourcemap plugin, so it's only necessary to use the production task when preparing to push to your production environment.