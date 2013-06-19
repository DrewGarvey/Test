/*
Copyright Ben Croker 
http:www.putyourlightson.net
*/

(function(){

 var h1 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h1"}).apply(editor.document);
  }
 },
 h2 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h2"}).apply(editor.document);
  }
 },
 h3 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h3"}).apply(editor.document);
  }
 },
 h4 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h4"}).apply(editor.document);
  }
 },
 h5 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h5"}).apply(editor.document);
  }
 },
 h6 = {
  exec:function(editor){
   new CKEDITOR.style({element: "h6"}).apply(editor.document);
  }
 };
 
 CKEDITOR.plugins.add("headers", {
  init: function(editor){
   for (var i = 1; i <= 6; i++) {
    editor.addCommand("h"+i, eval("h" + i));
    editor.ui.addButton("h" + i, {
     label: "Header " + i,
     icon: this.path + "images/h" + i + ".png",
     command: "h" + i
    });
   }
  }
 });
 
})();