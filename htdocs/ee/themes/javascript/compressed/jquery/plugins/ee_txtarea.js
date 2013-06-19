/*!
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

(function(){function e(a){this.el=a;this.lastIdx=-2;this.currentIdx=0;if(document.selection)this.range=this.el.createTextRange()}function f(a){this.el=a;this.sel=new d(this.el)}e.prototype={createSelection:function(a,b){this.el.focus();if("selectionStart"in this.el)this.el.selectionStart=a,this.el.selectionEnd=b;else if(document.selection){var c=document.selection.createRange();c.moveStart("character",-this.el.value.length);c.collapse();c.moveStart("character",a);c.moveEnd("character",b-a);c.select()}return this},
getSelectedText:function(){if("selectionStart"in this.el)return this.el.value.substr(this.el.selectionStart,this.el.selectionEnd-this.el.selectionStart);if(document.selection)return this.el.focus(),document.selection.createRange().text},getSelectedRange:function(){if("selectionStart"in this.el)return{start:this.el.selectionStart,end:this.el.selectionEnd};if(document.selection){var a=document.selection.createRange(),b=Math.abs(a.duplicate().moveEnd("character",-1E5));selectionStart=b-a.text.length;
return{start:selectionStart,end:b}}},replaceWith:function(a){var b;this.el.focus();if("selectionStart"in this.el)b=this.el.selectionStart+a.length,this.el.value=this.el.value.substr(0,this.el.selectionStart)+a+this.el.value.substr(this.el.selectionEnd,this.el.value.length),this.el.setSelectionRange(b,b);else if(document.selection)document.selection.createRange().text=a;return this},selectNext:function(a){if("selectionStart"in this.el){var b=this.currentIdx;chunk=0<b?this.el.value.substring(this.currentIdx):
this.el.value;this.currentIdx=chunk.indexOf(a);if(-1!=this.currentIdx)this.createSelection(b+this.currentIdx,b+this.currentIdx+a.length),this.lastIdx=b+this.currentIdx,this.currentIdx+=b+a.length;else if(this.lastIdx!=this.currentIdx)this.lastIdx=-1,this.currentIdx=0,this.selectNext(a)}else if(document.selection)this.el.focus(),this.range.findText(a,1,0)?(this.range.select(),this.range.collapse(!1)):this.range=this.el.createTextRange()},resetCycle:function(){this.lastIdx=-2;this.currentIdx=0;if(document.selection)this.range=
this.el.createTextRange()}};if(jQuery){var d=function(a){e.call(this,a);var a=$(this.el),b=a.scrollTop(9999).scrollTop(),c=a.val();a.val(c+"\n");new_height=a.scrollTop(9999).scrollTop();a.val(c).scrollTop(0);this.textarea_line_height=new_height-b;this.jQ_el=a},g=function(){};g.prototype=e.prototype;d.prototype=new g;d.prototype.constructor=d;d.prototype.scrollToCursor=function(){if("selectionStart"in this.el){for(var a=this.getSelectedRange(),a=this.jQ_el.val().substr(0,a.start).split("\n"),b=a.length,
c=0;c<a.length;c++)length_ratio=a[c].length/this.el.cols,1<length_ratio&&(b+=Math.ceil(length_ratio));this.jQ_el.scrollTop(((5<b?b-5:0)-5)*this.textarea_line_height)}return this}}else d=e;f.prototype={getSelectionObj:function(){return this.sel},createSelection:function(a,b){return this.sel.createSelection(a,b)},getSelectedText:function(){return this.sel.getSelectedText()},getSelectedRange:function(){return this.sel.getSelectedRange()},insertAtCursor:function(a){this.sel.replaceWith(a)},selectNext:function(a){this.sel.selectNext(a);
return this.sel},_resize:function(){var a=this.sel.getSelectedRange();a.start==a.end&&a.end==this.el.value.length&&(this.el.value+="\n",this.sel.createSelection(a.end,a.end));this.el.scrollHeight>this.el.clientHeight&&$(this.el).height(this.el.scrollHeight+10)},autoResize:function(){var a=this,b=$(this.el);b.css("overflow","hidden");b.keypress(function(){a._resize()});b.keyup(function(b){13==b.keyCode&&a._resize()})}};if(jQuery)for(func in f.prototype)jQuery.fn[func]=function(a){return function(){var b=
Array.prototype.slice.call(arguments),c=this.data("txtarea");c||(c=new f(this[0]),this.data("txtarea",c));return c[a].apply(c,b)}}(func);window.Txtarea=f})();
