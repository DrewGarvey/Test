CKEDITOR.config.toolbar_BSDBasic = [
    ['FontSize','Format','Bold','Italic','Underline','TextColor','-','JustifyLeft','JustifyCenter','JustifyRight','-','BulletedList','NumberedList','Outdent','Indent','Blockquote','-','Link','Unlink','Anchor','Image','Flash','-','SpecialChar','Table','PasteFromWord','PasteText','RemoveFormat','-','Source']
];
CKEDITOR.config.toolbar = 'BSDBasic';
CKEDITOR.config.fontSize_sizes = '1 (8pt)/8px;2 (10pt)/10px;3 (12pt)/12px;4 (14pt)/14px;5 (18pt)/18px;6 (24pt)/24px;7 (36pt)/36px';
CKEDITOR.config.resize_dir = 'vertical';
CKEDITOR.config.forcePasteAsPlainText = true;
CKEDITOR.on('dialogDefinition', function(ev) {
    // Take the dialog name and its definition from the event data.
    var dialogDefinition = ev.data.definition;
    dialogDefinition.buttons = [  CKEDITOR.dialog.cancelButton, CKEDITOR.dialog.okButton ];
});
