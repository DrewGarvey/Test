module.exports = function(grunt) {
    "use strict";
    
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        
        
        
        // The lint task will run the build configuration and the application
        // JavaScript through JSHint and report any errors.  You can change the
        // options for this task, by reading this:
        // https://github.com/cowboy/grunt/blob/master/docs/task_lint.md
      
        // Set our jshint options here:
        // http://www.jshint.com/options/
        jshint: {
            options: {
                curly: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                unused: true,
                boss: true,
                eqnull: true,
                browser: true,
                globals: {
                    console: false,
                    FB: false,
                    google: false,
                    jQuery: false,
                    _: false
                }
            },
            all: {
                src: ['htdocs/js/main.js']
            }
        },
        
        // The concatenate task is used here to all libraries from the lib
        // folder.  We exclude jQuery, because we call that from Google's
        // CDN.
        // You may or may not want to include the JS we write depending on how
        // often it changes.
        
        concat: {
            options: {
                separator: ';'
            },
            dev: {
                src: ["htdocs/js/lib/!(jquery-1.8.3)*.js", "htdocs/js/plugins.js"],
                dest: "htdocs/js/source.dev.js"
            },
            dist: {
                src: ["htdocs/js/lib/!(jquery-1.8.3)*.js", "htdocs/js/plugins.js"],
                dest: "dist/assets/js/source.js"
            }
        },
        
        clean: {
            clean: ['dist/*']
        },
        
        uglify: {
            options: {
              banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %> */'
            },
            
            // Takes the built sourcejs file and minifies it for filesize benefits.
            // Create a minified version in case you need to test minification locally.
            min: {
                src: ["dist/assets/js/source.js"],
                dest: "dist/assets/js/source.min.js"
            }
        },
          
        sass: {
            dist: {
                options: {
                    style: 'compact',
                    compass: 1,
                    debugInfo: false,
                    lineNumbers: false
                },
                files: {
                    'dist/assets/css/styles.css': 'htdocs/scss/styles.scss', // 'destination': 'source'
                    'dist/assets/css/styles-desktop.css': 'htdocs/scss/styles-desktop.scss'
                }
            },
            dev: {
                options: {
                    style: 'expanded',
                    compass: 1,
                    lineNumbers: 1
                },
                files: {
                    'htdocs/css/styles.dev.css': 'htdocs/scss/styles.scss',
                    'htdocs/css/styles-desktop.dev.css': 'htdocs/scss/styles-desktop.scss'
                }
            }
        },
        mountee: {
            site1: {
                src: 'templates/default_site',
                dest: 'mountee/default_site',
                volume: 'clientname.bluestatedigital.com'
            }
        },
        
        // This sets up watch configurations
        watch: {
            all: {
                files: ['htdocs/scss/*.scss', 'htdocs/js/*.js'],
                tasks: ['default']
            },
            sass: {
                files: ['htdocs/scss/*.scss'],
                tasks: ['sass:dev']
            }
        },
        copy: {
            dist: {
                files: {
                  "dist/mountee/<%= pkg.sites[0].short_name %>/js/source-min.js.js": "dist/assets/js/source.min.js",
                  "dist/mountee/<%= pkg.sites[0].short_name %>/js/source.js.js": "dist/assets/js/source.js",
                  "dist/assets/js/main.js": "htdocs/js/main.js",
                  "dist/mountee/<%= pkg.sites[0].short_name %>/js/main.js.js": "htdocs/js/main.js",
                  "dist/mountee/<%= pkg.sites[0].short_name %>/styles/index.css.css": "dist/assets/css/styles.css",
                  "dist/mountee/<%= pkg.sites[0].short_name %>/styles/index-desktop.css.css": "dist/assets/css/styles-desktop.css"
                }
            }
        },
    });
    
    
    // Load the grunt plugins
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.loadTasks('grunt');

    // Default task(s).
    grunt.registerTask("common", ['clean', 'jshint:all', 'concat', 'uglify']);
    grunt.registerTask("default", ['common', 'sass:dev', 'watch:sass']);
    grunt.registerTask("release", ['common', 'sass:dist', 'copy', 'mountee:site1']);
    grunt.registerTask("deploy", ['common', 'sass:dist', 'copy', 'mountee:site1:deploy']);
    


};
