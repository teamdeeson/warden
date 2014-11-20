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
        dest: '../src/Deeson/WardenBundle/Resources/public/assets',
        src: ["./css/**", "./fonts/**", "./img/**", "./js/**"]
      }
    },
    watch: {
      files: "./less/*",
      tasks: ["less", "copy"]
    }
  });

  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-copy');

};