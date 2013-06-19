/*
 * Grunt Task File
 * ---------------
 *
 * Task: Mounteee
 * Description: Take an EE Template as files directory, and save it into mountee format
 * Dependencies:
 *
 */

// == syntax
// mountee: {
//   site1: {
//     src: 'templates/default_site',
//     dest: 'mountee/default_site'
//     volume: 'client.bluestatedigital.com default'
//   }
// }
//
// or, for multisite:

// mountee: {
//   default: {
//     src: 'templates/default_site',
//     dest: 'mountee/default_site'
//     volume: 'client.bluestatedigital.com default'
//   },
//   msm: {
//     src: 'templates/msm',
//     dest: 'mountee/msm'
//     volume: 'client-msm.bluestatedigital.com msm'
//   }
// }

module.exports = function(grunt) {
  // External libs.

  var fs = require('fs');
  var crypto = require('crypto');
  
  // Grunt Aliases
  var _ = grunt.util._;
  var log = grunt.log;
  var skip = /DS_Store|sass_cache/; //Skip these files
  
  // ==========================================================================
  // HELPERS
  // ==========================================================================
  
  var mountify = function(file, src) {

    grunt.log.verbose.writeln('found a file: ' + file);

    // stip .group from template name
    file = file.replace('.group','');

    // .html and .feed need to change for mountee
    file = file.replace('.html', '.php');
    file = file.replace('.feed', '.rss');

    // get rid of the templates/site_name/
    file = file.replace(src + '/', '');

    grunt.log.verbose.writeln('changed it to: ' + file);
    return file;

  };

  var mounteeWrite = function(destpath, volume, deploy) {

    var dist = grunt.file.expand({dot:true},destpath + '**');
    var mountee = grunt.file.expand({dot:true},volume + '/**');
    var writeCount = 0;
    var re = /Global Variables|Snippets/;

    // Don't need global vars/snippets
    // TODO: but we do want certain specified low vars
    mountee = mountee.filter(function(file){
      if (!re.test(file)) {
        return file;
      }
    });

    // Hack to skip low vars (-jg)
    var re2 = /low_vars|styles.group|DS_Store/;
    dist = dist.filter(function(file){
      if(!re2.test(file)) {
        return file;
      }
    });

    if (dist.length === 0) {
      grunt.fatal("Did not find any matching files to copy to Mounted Volume.");
    }

    grunt.log.ok('Looping through dist directory to find templates to write:');

    
    grunt.file.recurse(destpath, function(abspath, rootdir, subdir, filename){
      if (!skip.test(filename)){
        var hash = crypto.createHash('md5');
        var desthash = crypto.createHash('md5');
        var template = '/' + subdir + '/' + filename;
        var destfile = volume + template;
        var index = mountee.indexOf(destfile);
        var contents = '';
        var dhash = '';

        // get the hash for each file in dist
        hash.update(fs.readFileSync(abspath));
        mHash = hash.digest('hex');
        grunt.log.verbose.writeln('Hashing "' + filename + '"... ' + mHash);

        // check to see if that file is in the list of mountee files
        if (index > -1 ) {

          // generate mountee version hash
          desthash.update(fs.readFileSync(destfile));
          dHash = desthash.digest('hex');
          grunt.log.verbose.writeln('Hashing "' + destfile + '"... ' + dHash);

          // compare and write or skip
          if (dHash != mHash) {
            if (deploy) {
              contents = grunt.file.read(abspath);
              grunt.file.write( destfile, contents );
              // Fail task if errors were logged.
              if (this.errorCount) { return false; }
              // Otherwise, print a success message.
              grunt.log.write('Updating "' + destfile + '"...').ok();
            } else {
              grunt.log.write('Would Update "' + destfile + '"...').ok();
            }
            writeCount ++;
          } else {
            grunt.log.verbose.writeln('Skipping "' + destfile + '"...');
          }

          // remove from list of mountee files
          mountee.splice(index, 1);

        } else {
          if (deploy) {
            contents = grunt.file.read(abspath);
            grunt.file.write( destfile, contents );
            // Fail task if errors were logged.
            if (this.errorCount) { return false; }
            // Otherwise, print a success message.
            grunt.log.write('Creating "' + destfile + '"...').ok();
          } else {
            grunt.log.write('Would Create "' + destfile + '"...').ok();
          }
          writeCount++;
        }
      }
    });

    // are there any files left in the mountee array which don't exist in dist
    // todo: add an option to delete

    if (writeCount === 0) {
      grunt.log.writeln('No templates written to mounted volume');
    }

    if (mountee.length > 0) {
      grunt.log.error('These files exist only in the mounted volume');
      grunt.log.error('They may need to be manually deleted:');
      mountee.forEach(function(file) {
        if (grunt.file.isFile(file)) grunt.log.writeln(file);
      });
    }

  };


  /**
   * Helper: hash
   * Description: generate a hash of a file or files.
   * Contributor: @necolas
   */
  var hash = function(files, opts) {
      opts = opts || {};
      var algorithm = opts.algorithm || 'md5';
      var encoding = opts.encoding || 'hex';
      var cwd = opts.cwd || process.cwd();
      var hash = crypto.createHash(algorithm);

      files = Array.isArray(files) ? files : [files];
      filelist = grunt.file.expand({cwd: cwd}, files);

      if (filelist.length === 0) {
          grunt.fatal("Should have an array of valid files to hash by now.");
      }

      filelist.forEach(function(file) {
          hash.update(fs.readFileSync(file));
          grunt.log.verbose.write('Hashing "' + file + '"...').ok();
      });

      return hash.digest(encoding);

  };
  
  // ==========================================================================
  // TASKS
  // ==========================================================================

  // todo: not sure if we want a multiTask or a single task.  we only want to run this on a single target
  grunt.registerMultiTask('mountee', 'Goes from EE templates as files to Mountee format.', function(arg1) {
    // this.data = the mountee task settings in Gruntfile.js
    var src = this.data.src,
        mounteeDest = 'dist/' + this.data.dest,
        volume = '/Volumes/' + this.data.volume,
        files = grunt.file.expand({dot:true, nonull:true}, src + '/**'),
        deploy = false;
        // console.dir(this.data);
    // test to see if we're deploying, if so, set deploy to true
    if (this.args == 'deploy') {
      deploy = true;
    }

    // create a mountee friendly version of the template dir
    grunt.file.recurse(src, function(abspath, rootdir, subdir, filename){
      if (!skip.test(filename)){
        grunt.log.verbose.writeln('Writing ' + abspath + ' to ' + mounteeDest + '/' + mountify(subdir + '/' + filename, src));
        grunt.file.copy(abspath, mounteeDest + '/' + mountify(subdir + '/' + filename, src));
      }
    });

    // files.forEach(function(file) {
    //   var destpath = mountify(file, src);
    //   grunt.file.copy(file, mountee + destpath);
    // });

    // todo: validate volume path/connection before trying to write
    grunt.log.verbose.writeln('Going to compare to: ' + volume);
    mounteeWrite(mounteeDest, volume, deploy);

  });

};