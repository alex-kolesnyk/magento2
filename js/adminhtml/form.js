var varienForm = new Class.create();

varienForm.prototype = {
    initialize : function(formId){
        this.formId = formId;
        this.validator  = new Validation(this.formId, {onElementValidate : this.checkErrors.bind(this)});
        this.errorSections = new Hash();
    },
    
    checkErrors : function(result, elm){
        if(!result)
            elm.setHasError(true, this);
        else
            elm.setHasError(false, this);
    },
    
    submit : function(){
        this.errorSections = new Hash();
        if(this.validator.validate()){
            $(this.formId).submit();
            return true;
        }
        return false;
    }
}

/**
 * redeclare Validation.isVisible function
 * 
 * use for not visible elements validation
 */
Validation.isVisible = function(elm){
    while(elm.tagName != 'BODY') {
        if(Element.hasClassName(elm, 'template') && Element.hasClassName(elm, 'no-display')){
            return false;
        }
        elm = elm.parentNode;
    }
    return true;
}

/**
 *  Additional elements methods
 */
var varienElementMethods = {
    setHasChanges : function(element, event){
        var elm = element;
        while(elm.tagName != 'BODY') {
            if(elm.statusBar)
                Element.addClassName($(elm.statusBar), 'changed')
            elm = elm.parentNode;
        }            
    },
    setHasError : function(element, flag, form){
        var elm = element;
        while(elm.tagName != 'BODY') {
            if(elm.statusBar){
                if(form.errorSections.keys().indexOf(elm.statusBar.id)<0) 
                    form.errorSections[elm.statusBar.id] = flag;
                if(flag){
                    Element.addClassName($(elm.statusBar), 'error');
                    form.errorSections[elm.statusBar.id] = flag;
                }
                else if(!form.errorSections[elm.statusBar.id]){
                    Element.removeClassName($(elm.statusBar), 'error')
                }
            }
                
                //flag ? Element.addClassName($(elm.statusBar), 'error') : Element.removeClassName($(elm.statusBar), 'error')

            /*if(!elm.visible() && elm.container)
                elm.container.show(elm);*/

            elm = elm.parentNode;
        }
        this.canShowElement = false;
    }
}

Element.addMethods(varienElementMethods);

// Global bind changes
function varienWindowOnload(){
    var dataElements = $$('input', 'select', 'textarea');
    for(var i in dataElements){
        if(dataElements[i] && dataElements[i].id){
            Event.observe(dataElements[i], 'change', dataElements[i].setHasChanges);
        }
    }
}
Event.observe(window, 'load', varienWindowOnload);