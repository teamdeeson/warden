module.exports = function(grunt) {
  grunt.initConfig({
    less: {
      development: {
        options: {
          paths: ["./less"],
          yuicompress: true
        },
        files: {
          "./css/AdminLTE.css": "./less/AdminLTE.less"
        }
      }
    },
    copy: {
      files: {
        expand: true,
        dest: '../docroot/sites/all/themes/rct_bootstrap',
        src: 'assets/**'
      }
    }
    /*watch: {
      files: "./less*//*",
      tasks: ["less", "copy"]
    }
     /Applications/MAMP/htdocs/warden/src/Deeson/WardenBundle/Resources/public/assets
    */
  });
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-watch');
};