VarienForm = Class.create();
VarienForm.prototype = {
    initialize: function(formId){
        this.cache      = $A();
        this.currLoader = false;
        this.currDataIndex = false; 
        this.form       = $(formId);
        this.validator  = new Validation(this.form);
        this.elementFocus   = this.elementOnFocus.bindAsEventListener(this);
        this.elementBlur    = this.elementOnBlur.bindAsEventListener(this);
        this.childLoader    = this.elementChildLoad.bindAsEventListener(this);
        this.highlightClass = 'highlight';
        this.bindElements();
        Form.Element.focus(Form.findFirstElement(this.form))
    },

    bindElements:function (){
        var elements = Form.getElements(this.form);
        for (var row in elements) {
            if (elements[row].id) {
                Event.observe(elements[row],'focus',this.elementFocus);
                Event.observe(elements[row],'blur',this.elementBlur);
            }
        }
    },

    elementOnFocus: function(event){
        var element = Event.findElement(event, 'fieldset');
        Element.addClassName(element, this.highlightClass);
    },

    elementOnBlur: function(event){
        var element = Event.findElement(event, 'fieldset');
        Element.removeClassName(element, this.highlightClass);
    },
    
    setElementsRelation: function(parent, child, dataUrl){
        if (parent=$(parent)) {
            // TODO: array of relation and caching
            if (!this.cache[parent.id]){
                this.cache[parent.id] = $A();
                this.cache[parent.id]['child']     = child;
                this.cache[parent.id]['dataUrl']   = dataUrl;
                this.cache[parent.id]['data']      = $A();
            }
            Event.observe(parent,'change',this.childLoader);
        }
    },
    
    elementChildLoad: function(event){
        element = Event.element(event);
        if (element.value) {
            this.currLoader = element.id;
            this.currDataIndex = element.value;
            if (this.cache[element.id]['data'][element.value]) {
                this.setDataToChild(this.cache[element.id]['data'][element.value]);
            }
            else{
                new Ajax.Request(this.cache[this.currLoader]['dataUrl'],{
                        method: 'post',
                        parameters: {"parent":element.value},
                        onComplete: this.reloadChildren.bind(this)
                });
            }
        }
    },
    
    reloadChildren: function(transport){
        var data = eval('(' + transport.responseText + ')');
        this.cache[this.currLoader]['data'][this.currDataIndex] = data;
        this.setDataToChild(data);
    },
    
    setDataToChild: function(data){
        if (data.length) {
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<select name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'">';
                for (var i in data){
                    if(data[i].value) html+= '<option value="'+data[i].value+'"';
                    if(data[i].index && child.value && child.value == data[i].index){
                        html+= ' selected';
                    }
                    html+='>'+data[i].label+'</option>';
                }
                html+= '</select>';
                new Insertion.Before(child,html);
                Element.remove(child);
            }
        }
        else{
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<input type="text" name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'">';
                new Insertion.Before(child,html);
                Element.remove(child);
            }
        }
        this.bindElements();
    }
}