CKEDITOR.editorConfig = function (config) {
    config.toolbarGroups = [
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
        {name: 'forms'},
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
        {name: 'links'},
        {name: 'insert'},
        {name: 'styles'},
        {name: 'colors'},
        {name: 'tools'},
        {name: 'others'},
        {name: 'about'}
    ];
    config.removeButtons = 'Cut,Copy,Paste,Undo,Redo,Anchor';
    config.removeDialogTabs = 'link:advanced;link:target';
    config.height = '30rem';
    config.disableNativeSpellChecker = true;
    config.entities = false;
    config.entities_additional = '';
    config.entities_greek = false;
    config.entities_latin = false;
};
