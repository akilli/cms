class e{constructor(e,t,i){if(!(e instanceof l&&t&&"string"==typeof t&&i&&"string"==typeof i))throw"Invalid argument";this.editor=e,this.name=t,this.target=i}convert(e){return this.editor.createElement(this.target,{content:e.innerHTML,html:!0})}}class t{constructor(e,t){if(!e||"string"!=typeof e)throw"Invalid argument";this.name=e,this.data=t}get(e){return this.data[e]||e}}class i{constructor(e,i){if(!(e instanceof l&&i&&"string"==typeof i))throw"Invalid argument";this.editor=e,this.name=i,this.translator=this.editor.translators.get(this.name)||new t(this.name,{})}open(e,t={}){this.editor.document.querySelectorAll("dialog.editor-dialog").forEach(e=>e.parentElement.removeChild(e));const i=this.editor.window.getSelection(),n=i.rangeCount>0?i.getRangeAt(0):null,r=this.editor.createElement("dialog",{attributes:{class:"editor-dialog"}}),s=this.editor.createElement("form"),a=this.editor.createElement("fieldset"),o=this.editor.createElement("button",{attributes:{type:"button","data-action":"cancel"},content:this.translator.get("Cancel")}),l=this.editor.createElement("button",{attributes:{"data-action":"save"},content:this.translator.get("Save")}),d=()=>{n&&(i.removeAllRanges(),i.addRange(n)),r.parentElement.removeChild(r)};r.appendChild(s),r.addEventListener("click",e=>{e.target===r&&d()}),"boolean"!=typeof r.open&&this.polyfill(r),r.open=!0,a.insertAdjacentHTML("beforeend",this.getFieldsetHtml());for(let[e,i]of Object.entries(t))a.elements[e]&&(a.elements[e].value=i);s.appendChild(a),s.addEventListener("submit",t=>{t.preventDefault(),d();const i={};Array.from(a.elements).forEach(e=>i[e.name]=e.value),e(i)}),o.addEventListener("click",d),s.appendChild(o),s.appendChild(l),this.editor.element.appendChild(r)}getFieldsetHtml(){throw"Not implemented"}polyfill(e){Object.defineProperty(e,"open",{get:function(){return this.hasAttribute("open")},set:function(e){e?this.setAttribute("open",""):this.removeAttribute("open")}})}}class n{constructor(e,t){if(!(e instanceof l&&t&&"string"==typeof t))throw"Invalid argument";this.editor=e,this.name=t}filter(e){throw"Not implemented"}}class r{constructor(e){if(!(e instanceof l))throw"Invalid argument";this.editor=e}observe(e){throw"Not implemented"}}class s{constructor({name:e,group:t,attributes:i=[],children:n=[],editable:r=!1,empty:s=!1,enter:a=null}={}){if(!e||"string"!=typeof e||!t||"string"!=typeof t)throw"Invalid argument";this.name=e,this.group=t,this.children=Array.isArray(n)?n:[],this.attributes=Array.isArray(i)?i:[],this.editable=Boolean(r),this.empty=Boolean(s),this.enter=a&&"string"==typeof a?a:null}}class a{constructor(e,t){if(!(e instanceof l&&t&&"string"==typeof t))throw"Invalid argument";this.editor=e,this.name=t}init(){throw"Not implemented"}registerTranslator(e){this.editor.translators.set(new t(this.name,e[this.editor.config.base.lang]||{}))}registerTag(e){this.editor.tags.set(new s(e))}static defaultConfig(){return{}}}class o extends Map{constructor(e,t=[]){super(),this.type=e,t.forEach(e=>this.set(e))}set(e){if(!(e instanceof this.type&&e.hasOwnProperty("name")))throw"Invalid argument";super.set(e.name,e)}}class l{constructor(r,l={}){if(!(r instanceof HTMLElement)||l&&"object"!=typeof l)throw"Invalid argument";this.orig=r,this.document=this.orig.ownerDocument,this.window=this.document.defaultView,this.origin=this.window.origin||this.window.location.origin,this.element=this.createElement("div",{attributes:{class:"editor"}}),this.content=this.createElement("div",{attributes:{class:"editor-content"}}),this.toolbar=this.createElement("div",{attributes:{class:"editor-toolbar"}}),this.config=l,this.translators=new o(t),this.tags=new o(s),this.converters=new o(e),this.filters=new o(n),this.dialogs=new o(i),this.commands=new o(d),this.plugins=new o(a)}init(){0===this.plugins.size&&(this.initChildren(),this.initConfig(),this.initPlugins(),this.initToolbar()),this.initContent(),this.initDom()}initChildren(){this.element.appendChild(this.toolbar),this.element.appendChild(this.content)}initConfig(){for(let[e,t]of Object.entries(this.constructor.defaultConfig()))this.config[e]=Object.assign({},t,this.config[e]||{})}initPlugins(){this.config.base.plugins.map(e=>{const t=e.defaultConfig(),i=new e(this);Object.keys(t).length>0&&(this.config[i.name]=Object.assign({},t,this.config[i.name]||{})),this.plugins.set(i)}),this.plugins.forEach(e=>e.init())}initToolbar(){this.config.base.toolbar.forEach(e=>{if(!this.commands.has(e))throw"Invalid argument";const t=this.createElement("button",{attributes:{type:"button","data-cmd":e,title:e},content:e});t.addEventListener("click",()=>{this.window.getSelection().containsNode(this.content,!0)||this.content.focus(),this.commands.get(e).execute()}),this.toolbar.appendChild(t)})}initContent(){this.orig instanceof HTMLTextAreaElement?(this.content.innerHTML=this.orig.value.replace("/&nbsp;/g"," "),this.orig.form.addEventListener("submit",()=>this.save())):this.content.innerHTML=this.orig.innerHTML,this.filter(this.content)}initDom(){this.orig.insertAdjacentElement("afterend",this.element),this.orig.hidden=!0}destroy(){this.element.parentElement.removeChild(this.element),this.orig.hidden=!1}getData(){const e=this.content.cloneNode(!0);return this.filter(e),e.innerHTML}save(){this.orig instanceof HTMLTextAreaElement?this.orig.value=this.getData():this.orig.innerHTML=this.getData()}insert(e){let t;if(!(e instanceof HTMLElement&&(t=this.tags.get(e.tagName.toLowerCase()))))throw"Invalid argument";"text"===t.group?this.formatText(e):this.insertWidget(e)}insertWidget(e){if(!(e instanceof HTMLElement&&this.allowed(e.tagName.toLowerCase(),"root")))throw"Invalid argument";this.content.appendChild(e)}insertText(e){this.document.execCommand("inserttext",!1,e)}formatText(e){if(!(e instanceof HTMLElement))throw"Invalid argument";const t=this.window.getSelection(),i=t.anchorNode instanceof Text?t.anchorNode.parentElement:t.anchorNode,n=this.getSelectedEditable();if(t.isCollapsed||!t.toString().trim()||!(i instanceof HTMLElement)||!n)return;const r=t.getRangeAt(0),s=this.tags.get(i.tagName.toLowerCase()),a=s&&"text"!==s.group?i:i.parentElement;r.startContainer instanceof Text&&!r.startContainer.parentElement.isSameNode(a)&&r.setStartBefore(r.startContainer.parentElement),r.endContainer instanceof Text&&!r.endContainer.parentElement.isSameNode(a)&&r.setEndAfter(r.endContainer.parentElement);const o=r.toString(),l=r.cloneContents().childNodes;let d=Array.from(l).every(t=>t instanceof Text&&!t.textContent.trim()||t instanceof HTMLElement&&t.tagName===e.tagName);r.deleteContents(),!(a.isContentEditable&&this.allowed(e.tagName.toLowerCase(),a.tagName.toLowerCase())&&o.trim())||s&&d?r.insertNode(this.createText(o)):(e.textContent=o,r.insertNode(e)),a.normalize()}registerElement(e,t,i=null){void 0===this.window.customElements.get(e)&&this.window.customElements.define(e,t,i?{extends:i}:null)}createElement(e,{attributes:t={},content:i="",html:n=!1,is:r=null}={}){const s=this.document.createElement(e,r?{is:r}:null);i&&n?s.innerHTML=i:i&&(s.textContent=i);for(let[e,i]of Object.entries(t))i&&s.setAttribute(e,""+i);return s}createText(e){return this.document.createTextNode(e)}getSelectedElement(){const e=this.window.getSelection(),t=e.anchorNode instanceof Text?e.anchorNode.parentElement:e.anchorNode,i=e.focusNode instanceof Text?e.focusNode.parentElement:e.focusNode;return t instanceof HTMLElement&&i instanceof HTMLElement&&t.isSameNode(i)&&this.content.contains(t)?t:null}getSelectedEditable(){const e=this.window.getSelection(),t=e.anchorNode instanceof Text?e.anchorNode.parentElement:e.anchorNode,i=e.focusNode instanceof Text?e.focusNode.parentElement:e.focusNode;if(t instanceof HTMLElement&&i instanceof HTMLElement){const e=t.closest("[contenteditable=true]"),n=i.closest("[contenteditable=true]");if(e instanceof HTMLElement&&n instanceof HTMLElement&&e.isSameNode(n)&&this.content.contains(e))return e}return null}getSelectedWidget(){const e=this.getSelectedElement();return e?e.closest("div.editor-content > *"):null}focusEnd(e){if(!(e instanceof HTMLElement))throw"Invalid argument";const t=this.document.createRange(),i=this.window.getSelection();e.focus(),t.selectNodeContents(e),t.collapse(),i.removeAllRanges(),i.addRange(t)}observe(e,t={childList:!0,subtree:!0}){if(!(e instanceof r))throw"Invalid argument";new MutationObserver(t=>e.observe(t)).observe(this.content,t)}filter(e){if(!(e instanceof HTMLElement))throw"Invalid argument";this.filters.forEach(t=>{e.normalize(),t.filter(e)})}convert(e){if(!(e instanceof HTMLElement))throw"Invalid argument";const t=this.converters.get(e.tagName.toLowerCase());if(!t)return e;const i=t.convert(e);return e.parentElement.replaceChild(i,e),i}isRoot(e){if(!(e instanceof HTMLElement))throw"Invalid argument";return this.content.isSameNode(e)||"editor-content"===e.getAttribute("class")}getTagName(e){return this.isRoot(e)?"root":e.tagName.toLowerCase()}allowed(e,t){const i=this.tags.get(e),n=i?i.group:e,r=this.tags.get(t);return r&&r.children.includes(n)}url(e){const t=this.createElement("a",{attributes:{href:e}});return t.origin===this.origin?t.pathname:t.href}static defaultConfig(){return{}}static create(e,t={}){const i=new this(e,t);return i.init(),i}}class d{constructor(e,t,i=null){if(!(e instanceof l)||!t||"string"!=typeof t||i&&"string"!=typeof i)throw"Invalid argument";this.editor=e,this.name=t,this.tagName=i?i.toLowerCase():null,this.dialog=this.editor.dialogs.get(this.name)||null}execute(){this.dialog?this.dialog.open(e=>this.insert(e),this.selectedAttributes()):this.insert()}insert(e={}){this.tagName&&this.editor.insert(this.editor.createElement(this.tagName,{attributes:e}))}selectedAttributes(){const e={},t=this.editor.getSelectedElement();return t instanceof HTMLElement&&t.tagName.toLowerCase()===this.tagName&&Array.from(t.attributes).forEach(t=>e[t.nodeName]=t.nodeValue),e}}class h extends d{insert({src:e,caption:t="",width:i="",height:n="",controls:r="controls"}={}){if(!e)throw"Invalid argument";const s=this.editor.createElement("figure",{attributes:{class:this.name}});s.appendChild(this.editor.createElement(this.tagName,{attributes:{src:this.editor.url(e),width:i,height:n,controls:r}})),s.appendChild(this.editor.createElement("figcaption",{content:t,html:!0})),this.editor.insert(s)}}class c extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Audio")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class m extends i{constructor(e,t,i){if(super(e,t),!i||"string"!=typeof i)throw"Invalid argument";this.url=i,this.opts=Object.assign({alwaysRaised:"yes",dependent:"yes",height:""+this.editor.window.screen.height,location:"no",menubar:"no",minimizable:"no",modal:"yes",resizable:"yes",scrollbars:"yes",toolbar:"no",width:""+this.editor.window.screen.width},this.editor.config.browser)}open(e,t={}){const i=Object.entries(this.opts).map(e=>`${e[0]}=${e[1]}`).join(","),n=this.editor.window.open(this.url,this.name,i),r=this.editor.createElement("a",{attributes:{href:this.url}});this.editor.window.addEventListener("message",t=>{t.origin===r.origin&&t.source===n&&(e(t.data),n.close())},!1)}}const g={de:{Audio:"Audio",Cancel:"Abbrechen",Height:"Höhe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Width:"Breite"}};class u extends a{constructor(e){super(e,"audio")}init(){this.registerTag({name:"audio",group:"media",attributes:["controls","height","src","width"],empty:!0}),this.registerTranslator(g),this.editor.config[this.name].browser?this.editor.dialogs.set(new m(this.editor,this.name,this.editor.config[this.name].browser)):this.editor.dialogs.set(new c(this.editor,this.name)),this.editor.commands.set(new h(this.editor,this.name,"audio"))}static defaultConfig(){return{browser:null}}}class p extends n{filter(e){const t=this.editor.getTagName(e),i=this.editor.isRoot(e);Array.from(e.childNodes).forEach(n=>{if(n instanceof HTMLElement&&(n=this.editor.convert(n)),n instanceof HTMLElement){const r=n.tagName.toLowerCase(),s=this.editor.tags.get(r),a=n.textContent.trim();s&&(this.editor.allowed(r,t)||i&&"text"===s.group&&this.editor.allowed("p",t))?(Array.from(n.attributes).forEach(e=>{s.attributes.includes(e.name)||n.removeAttribute(e.name)}),n.hasChildNodes()&&this.editor.filter(n),n.hasChildNodes()||s.empty?this.editor.allowed(r,t)||e.replaceChild(this.editor.createElement("p",{content:n.outerHTML,html:!0}),n):e.removeChild(n)):i&&a&&this.editor.allowed("p",t)?e.replaceChild(this.editor.createElement("p",{content:a}),n):a&&this.editor.allowed("text",t)?e.replaceChild(this.editor.createText(a),n):e.removeChild(n)}else if(n instanceof Text){const r=n.textContent.trim();i&&r&&this.editor.allowed("p",t)?e.replaceChild(this.editor.createElement("p",{content:r}),n):r&&this.editor.allowed("text",t)||e.removeChild(n)}else e.removeChild(n)}),e.innerHTML=e.innerHTML.replace(/^\s*(<br\s*\/?>\s*)+/gi,"").replace(/\s*(<br\s*\/?>\s*)+$/gi,""),e instanceof HTMLParagraphElement?e.outerHTML=e.outerHTML.replace(/\s*(<br\s*\/?>\s*){2,}/gi,"</p><p>"):e.innerHTML=e.innerHTML.replace(/\s*(<br\s*\/?>\s*){2,}/gi,"<br>")}}class f extends r{observe(e){const t=this.editables(),i=t.join(", ");e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&t.includes(e.tagName.toLowerCase())?(this.toEditable(e),e.parentElement instanceof HTMLElement&&!this.editor.isRoot(e.parentElement)&&e.focus()):i&&e instanceof HTMLElement&&e.querySelectorAll(i).forEach(e=>this.toEditable(e))}))}editables(){return[...this.editor.tags].reduce((e,t)=>(t[1].editable&&e.push(t[1].name),e),[])}toEditable(e){e.contentEditable="true",e.addEventListener("keydown",e=>{this.onKeydownEnter(e),this.onKeydownBackspace(e)}),e.addEventListener("keyup",e=>this.onKeyupEnter(e))}onKeydownEnter(e){"Enter"!==e.key||e.shiftKey&&this.editor.allowed("br",e.target.tagName.toLowerCase())||(e.preventDefault(),e.cancelBubble=!0)}onKeyupEnter(e){let t;if("Enter"===e.key&&!e.shiftKey&&(t=this.editor.tags.get(e.target.tagName.toLowerCase()))&&t.enter){let i,n=e.target;e.preventDefault(),e.cancelBubble=!0;do{if(i=this.editor.getTagName(n.parentElement),this.editor.allowed(t.enter,i)){n.insertAdjacentElement("afterend",this.editor.createElement(t.enter));break}}while((n=n.parentElement)&&this.editor.content.contains(n)&&!this.editor.isRoot(n))}}onKeydownBackspace(e){const t=this.editor.getSelectedWidget(),i=["blockquote","details","ol","ul"].includes(e.target.parentElement.tagName.toLowerCase())&&("summary"!==e.target.tagName.toLowerCase()||e.target.matches(":only-child"));if("Backspace"===e.key&&!e.shiftKey&&!e.target.textContent&&t&&(t.isSameNode(e.target)||i)){const i=t.isSameNode(e.target)||e.target.matches(":only-child")?t:e.target;i.previousElementSibling&&this.editor.focusEnd(i.previousElementSibling),i.parentElement.removeChild(i),e.preventDefault(),e.cancelBubble=!0}}}class b extends n{filter(e){e.querySelectorAll("figure > figcaption:only-child").forEach(e=>e.parentElement.removeChild(e))}}class w extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&"figure"===e.tagName.toLowerCase()&&(e.querySelector(":scope > figcaption")||e.appendChild(this.editor.createElement("figcaption")),this.editor.isRoot(e.parentElement)&&this.keyboard(e))}))}keyboard(e){e.addEventListener("keyup",t=>{const i={ArrowLeft:"left",ArrowRight:"right"};if(this.editor.document.activeElement.isSameNode(e)&&t.ctrlKey&&Object.keys(i).includes(t.key)){t.preventDefault(),t.cancelBubble=!0;const n=i[t.key]&&e.classList.contains(i[t.key]);e.classList.remove(...Object.values(i)),n||e.classList.add(i[t.key])}})}}class E extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&e.parentElement instanceof HTMLElement&&this.editor.isRoot(e.parentElement)&&(e.tabIndex=0,e.focus(),this.keyboard(e),this.dragndrop(e))}))}keyboard(e){e.addEventListener("keyup",t=>{this.editor.document.activeElement.isSameNode(e)&&t.ctrlKey&&["ArrowUp","ArrowDown","Delete"].includes(t.key)&&("ArrowUp"===t.key&&e.previousElementSibling?(e.previousElementSibling.insertAdjacentHTML("beforebegin",e.outerHTML),e.parentElement.removeChild(e)):"ArrowDown"===t.key&&e.nextElementSibling?(e.nextElementSibling.insertAdjacentHTML("afterend",e.outerHTML),e.parentElement.removeChild(e)):"Delete"===t.key&&e.parentElement.removeChild(e),t.preventDefault(),t.cancelBubble=!0)})}dragndrop(e){const t="text/x-editor-name",i=()=>{const t=e.hasAttribute("draggable");this.editor.content.querySelectorAll("[draggable]").forEach(e=>{e.removeAttribute("draggable"),e.hasAttribute("contenteditable")&&e.setAttribute("contenteditable","true")}),t||(e.setAttribute("draggable","true"),e.hasAttribute("contenteditable")&&e.setAttribute("contenteditable","false"))},n=i=>{const n=i.dataTransfer.getData(t);n&&this.editor.allowed(n,"root")&&(i.preventDefault(),e.setAttribute("data-dragover",""),i.dataTransfer.dropEffect="move")};e.addEventListener("dblclick",i),e.addEventListener("dragstart",i=>{i.dataTransfer.effectAllowed="move",i.dataTransfer.setData(t,e.tagName.toLowerCase()),i.dataTransfer.setData("text/x-editor-html",e.outerHTML)}),e.addEventListener("dragend",t=>{"move"===t.dataTransfer.dropEffect&&e.parentElement.removeChild(e),i()}),e.addEventListener("dragenter",n),e.addEventListener("dragover",n),e.addEventListener("dragleave",()=>e.removeAttribute("data-dragover")),e.addEventListener("drop",i=>{const n=i.dataTransfer.getData(t),r=i.dataTransfer.getData("text/x-editor-html");i.preventDefault(),e.removeAttribute("data-dragover"),n&&this.editor.allowed(n,"root")&&r&&e.insertAdjacentHTML("beforebegin",r)})}}class v extends a{constructor(e){super(e,"base")}init(){this.registerTag({name:"root",group:"root",children:["details","figure","heading","list","paragraph","section"]}),this.registerTag({name:"br",group:"break",empty:!0}),this.registerTag({name:"figure",group:"figure",attributes:["class"],children:["caption","media","quote","table"]}),this.registerTag({name:"figcaption",group:"caption",children:["text"],editable:!0,enter:"p"}),this.editor.observe(new f(this.editor)),this.editor.observe(new E(this.editor)),this.editor.observe(new w(this.editor)),this.editor.filters.set(new p(this.editor,this.name)),this.editor.filters.set(new b(this.editor,"figure"))}static defaultConfig(){return{browser:{},lang:null,plugins:[],tags:[],toolbar:[]}}}class y extends d{insert({id:e}={}){if(!e)throw"Invalid argument";this.editor.insert(this.editor.createElement(this.tagName,{attributes:{id:e}}))}}class T extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Block")}</legend>\n            <div data-attr="id" data-required>\n                <label for="editor-id">${this.translator.get("ID")}</label>\n                <input id="editor-id" name="id" type="text" required="required" />\n            </div>\n        `}}class L extends HTMLElement{constructor(){super(),this.attachShadow({mode:"open"})}get content(){return this.shadowRoot.innerHTML}set content(e){this.shadowRoot.innerHTML=e}}class C extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof L&&e.id&&this.editor.config.block.api&&this.initBlock(e)}))}async initBlock(e){try{const t=await fetch(this.editor.config.block.api.replace("{id}",e.id),{mode:"no-cors"});if(t.ok){let i="";this.editor.config.block.css&&(i=this.editor.config.block.css.split(",").map(e=>`<link rel="stylesheet" href="${e}">`).join(""));const n=await t.text();e.content=i+n}}catch(e){console.error(e)}}}const x={de:{Cancel:"Abbrechen",Height:"Höhe",Ifrane:"Iframe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Width:"Breite"}};class H extends a{constructor(e){super(e,"block")}init(){this.editor.registerElement("app-block",L),this.registerTag({name:"app-block",group:"section",attributes:["id"],empty:!0}),this.registerTranslator(x),this.editor.observe(new C(this.editor)),this.editor.config[this.name].browser?this.editor.dialogs.set(new m(this.editor,this.name,this.editor.config[this.name].browser)):this.editor.dialogs.set(new T(this.editor,this.name)),this.editor.commands.set(new y(this.editor,this.name,"app-block"))}static defaultConfig(){return{api:null,browser:null,css:null}}}class A extends d{insert(e={}){const t=this.editor.createElement(this.tagName);t.appendChild(this.editor.createElement("summary")),t.appendChild(this.editor.createElement("p")),this.editor.insert(t)}}class k extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLDetailsElement?this.initDetails(e):e instanceof HTMLElement&&"summary"===e.tagName.toLowerCase()?this.initSummary(e):e instanceof HTMLElement&&e.querySelectorAll("details").forEach(e=>this.initDetails(e))}))}initDetails(e){let t=e.firstElementChild;if(t instanceof HTMLElement&&"summary"===t.tagName.toLowerCase())this.initSummary(t),1===e.childElementCount&&e.appendChild(this.editor.createElement("p"));else if(t instanceof HTMLElement){const i=this.editor.createElement("summary");e.insertBefore(i,t),this.initSummary(i)}}initSummary(e){const t=()=>{e.textContent.trim()||(e.textContent=this.editor.translators.get("details").get("Details"))};t(),e.addEventListener("blur",t),e.addEventListener("keydown",e=>{" "===e.key&&(e.preventDefault(),e.cancelBubble=!0)}),e.addEventListener("keyup",e=>{" "===e.key&&(e.preventDefault(),e.cancelBubble=!0,this.editor.insertText(" "))})}}const N={de:{Details:"Details"}};class M extends a{constructor(e){super(e,"details")}init(){this.registerTag({name:"details",group:"details",children:["figure","list","paragraph","summary"]}),this.registerTag({name:"summary",group:"summary",editable:!0,enter:"p"}),this.editor.observe(new k(this.editor)),this.registerTranslator(N),this.editor.commands.set(new A(this.editor,this.name,"details"))}}class S extends d{execute(){this.editor.element.classList.contains("editor-fullscreen")?this.editor.element.classList.remove("editor-fullscreen"):this.editor.element.classList.add("editor-fullscreen")}}class R extends a{constructor(e){super(e,"fullscreen")}init(){this.editor.commands.set(new S(this.editor,this.name))}}class I extends a{constructor(e){super(e,"heading")}init(){this.registerTag({name:"h2",group:"heading",editable:!0,enter:"p"}),this.registerTag({name:"h3",group:"heading",editable:!0,enter:"p"}),this.editor.commands.set(new d(this.editor,this.name,"h2")),this.editor.commands.set(new d(this.editor,"subheading","h3"))}}class D extends d{insert({src:e,caption:t="",width:i="",height:n="",allowfullscreen:r="allowfullscreen"}={}){if(!e)throw"Invalid argument";const s=this.editor.createElement("figure",{attributes:{class:this.name}});s.appendChild(this.editor.createElement(this.tagName,{attributes:{src:this.editor.url(e),width:i,height:n,allowfullscreen:r}})),s.appendChild(this.editor.createElement("figcaption",{content:t,html:!0})),this.editor.insert(s)}}class U extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Iframe")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class $ extends a{constructor(e){super(e,"iframe")}init(){this.registerTag({name:"iframe",group:"media",attributes:["allowfullscreen","height","src","width"],empty:!0}),this.registerTranslator(x),this.editor.config[this.name].browser?this.editor.dialogs.set(new m(this.editor,this.name,this.editor.config[this.name].browser)):this.editor.dialogs.set(new U(this.editor,this.name)),this.editor.commands.set(new D(this.editor,this.name,"iframe"))}static defaultConfig(){return{browser:null}}}class q extends d{insert({src:e,caption:t="",width:i="",height:n="",alt:r=""}={}){if(!e)throw"Invalid argument";const s=this.editor.createElement("figure",{attributes:{class:this.name}});s.appendChild(this.editor.createElement(this.tagName,{attributes:{src:this.editor.url(e),width:i,height:n,alt:r}})),s.appendChild(this.editor.createElement("figcaption",{content:t,html:!0})),this.editor.insert(s)}}class B extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Image")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="alt">\n                <label for="editor-alt">${this.translator.get("Alternative text")}</label>\n                <input id="editor-alt" name="alt" type="text" placeholder="${this.translator.get("Replacement text for use when media elements are not available")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}const j={de:{"Alternative text":"Alternativtext",Cancel:"Abbrechen",Height:"Höhe",Image:"Bild","Insert URL to media element":"URL zum Medienelement eingeben","Replacement text for use when media elements are not available":"Ersatztext, falls Medienelemente nicht verfügbar sind",Save:"Speichern",URL:"URL",Width:"Breite"}};class K extends a{constructor(e){super(e,"image")}init(){this.registerTag({name:"img",group:"media",attributes:["alt","height","src","width"],empty:!0}),this.registerTranslator(j),this.editor.config[this.name].browser?this.editor.dialogs.set(new m(this.editor,this.name,this.editor.config[this.name].browser)):this.editor.dialogs.set(new B(this.editor,this.name)),this.editor.commands.set(new q(this.editor,this.name,"img"))}static defaultConfig(){return{browser:null}}}class O extends d{insert({href:e=null}={}){const t=this.editor.getSelectedElement(),i=t instanceof HTMLAnchorElement?t:null;e&&i?i.setAttribute("href",this.editor.url(e)):e?super.insert({href:this.editor.url(e)}):i&&i.parentElement.replaceChild(this.editor.createText(i.textContent),i)}}class z extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Link")}</legend>\n            <div data-attr="href">\n                <label for="editor-href">${this.translator.get("URL")}</label>\n                <input id="editor-href" name="href" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to add a link or leave empty to unlink")}" />\n            </div>\n        `}}const F={de:{Cancel:"Abbrechen","Insert URL to add a link or leave empty to unlink":"URL eingeben um zu verlinken oder leer lassen um Link zu entfernen",Link:"Link",Save:"Speichern",URL:"URL"}};class W extends a{constructor(e){super(e,"link")}init(){this.registerTag({name:"a",group:"text",attributes:["href"]}),this.registerTranslator(F),this.editor.dialogs.set(new z(this.editor,this.name)),this.editor.commands.set(new O(this.editor,this.name,"a"))}}class P extends d{insert(e={}){const t=this.editor.createElement(this.tagName);t.appendChild(this.editor.createElement("li")),this.editor.insert(t)}}class V extends a{constructor(e){super(e,"list")}init(){this.registerTag({name:"ul",group:"list",children:["listitem"]}),this.registerTag({name:"ol",group:"list",children:["listitem"]}),this.registerTag({name:"li",group:"listitem",children:["break","text"],editable:!0,enter:"li"}),this.editor.commands.set(new P(this.editor,"orderedlist","ol")),this.editor.commands.set(new P(this.editor,"unorderedlist","ul"))}}class Z extends a{constructor(e){super(e,"paragraph")}init(){this.registerTag({name:"p",group:"paragraph",children:["break","text"],editable:!0,enter:"p"}),this.editor.commands.set(new d(this.editor,this.name,"p"))}}class G extends d{insert(e={}){const t=this.editor.createElement(this.tagName);t.appendChild(this.editor.createElement("p"));const i=this.editor.createElement("figure",{attributes:{class:this.name}});i.appendChild(t),i.appendChild(this.editor.createElement("figcaption")),this.editor.insert(i)}}class J extends a{constructor(e){super(e,"quote")}init(){this.registerTag({name:"blockquote",group:"quote",children:["paragraph"]}),this.editor.commands.set(new G(this.editor,this.name,"blockquote"))}}class Q extends d{insert({rows:e=1,cols:t=1}={}){if(e<=0||t<=0)throw"Invalid argument";const i=this.editor.createElement(this.tagName);["thead","tbody","tfoot"].forEach(n=>{const r=this.editor.createElement(n),s="thead"===n?"th":"td",a="tbody"===n?e:1;let o;i.appendChild(r);for(let e=0;e<a;e++){o=this.editor.createElement("tr"),r.appendChild(o);for(let e=0;e<t;++e)o.appendChild(this.editor.createElement(s))}});const n=this.editor.createElement("figure",{attributes:{class:this.name}});n.appendChild(i),n.appendChild(this.editor.createElement("figcaption")),this.editor.insert(n)}}class X extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Table")}</legend>\n            <div data-attr="rows" data-required>\n                <label for="editor-rows">${this.translator.get("Rows")}</label>\n                <input id="editor-rows" name="rows" type="number" value="1" required="required" min="1" />\n            </div>\n            <div data-attr="cols" data-required>\n                <label for="editor-cols">${this.translator.get("Columns")}</label>\n                <input id="editor-cols" name="cols" type="number" value="1" required="required" min="1" />\n            </div>\n        `}}class Y extends n{filter(e){if(e instanceof HTMLTableRowElement&&!e.querySelector("th:not(:empty), td:not(:empty)")||e instanceof HTMLTableSectionElement&&e.rows.length<=0||e instanceof HTMLTableElement&&e.querySelector("thead, tfoot")&&!e.querySelector("tbody"))for(;e.firstChild;)e.removeChild(e.firstChild)}}class _ extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLTableElement?this.initTable(e):e instanceof HTMLElement&&e.querySelectorAll("table").forEach(e=>this.initTable(e))}))}initTable(e){this.sections(e),this.keyboard(e)}sections(e){if(e.tBodies.length>0&&e.tBodies[0].rows[0]&&(!e.tHead||!e.tFoot)){const t=e.tBodies[0].rows[0].cells.length;let i;if(!e.tHead){i=e.createTHead().insertRow();for(let e=0;e<t;e++)i.appendChild(this.editor.createElement("th"))}if(!e.tFoot){i=e.createTFoot().insertRow();for(let e=0;e<t;e++)i.insertCell()}}}keyboard(e){e.addEventListener("keydown",t=>{const i=t.target,n=i.parentElement,r=n.parentElement;if(i instanceof HTMLTableCellElement&&n instanceof HTMLTableRowElement&&(r instanceof HTMLTableElement||r instanceof HTMLTableSectionElement)&&(t.altKey||t.ctrlKey)&&["ArrowLeft","ArrowRight","ArrowUp","ArrowDown"].includes(t.key)){const s=n.cells.length,a=r.rows.length,o=r instanceof HTMLTableElement?n.rowIndex:n.sectionRowIndex;let l;if(t.altKey&&("ArrowLeft"===t.key&&i.cellIndex>0||"ArrowRight"===t.key&&i.cellIndex<s-1))l=i.cellIndex+("ArrowLeft"===t.key?-1:1),Array.from(e.rows).forEach(e=>e.deleteCell(l));else if(t.altKey&&("ArrowUp"===t.key&&o>0||"ArrowDown"===t.key&&o<a-1))l=o+("ArrowUp"===t.key?-1:1),r.deleteRow(l);else if(!t.ctrlKey||"ArrowLeft"!==t.key&&"ArrowRight"!==t.key){if(t.ctrlKey&&("ArrowUp"===t.key||"ArrowDown"===t.key)){l=o+("ArrowUp"===t.key?0:1);const e=r.insertRow(l);for(let t=0;t<s;t++)e.insertCell()}}else l=i.cellIndex+("ArrowLeft"===t.key?0:1),Array.from(e.rows).forEach(e=>{e.querySelector(":scope > td")?e.insertCell(l):e.insertBefore(this.editor.createElement("th"),e.cells[l])});t.preventDefault(),t.cancelBubble=!0}})}}const ee={de:{Cancel:"Abbrechen",Columns:"Spalten",Rows:"Zeilen",Save:"Speichern",Table:"Tabelle"}};class te extends a{constructor(e){super(e,"table")}init(){this.registerTag({name:"table",group:"table",children:["tablesection"]}),this.registerTag({name:"thead",group:"tablesection",children:["tablerow"]}),this.registerTag({name:"tbody",group:"tablesection",children:["tablerow"]}),this.registerTag({name:"tfoot",group:"tablesection",children:["tablerow"]}),this.registerTag({name:"tr",group:"tablerow",children:["tablecell"]}),this.registerTag({name:"th",group:"tablecell",children:["break","text"],editable:!0,empty:!0}),this.registerTag({name:"td",group:"tablecell",children:["break","text"],editable:!0,empty:!0}),this.editor.observe(new _(this.editor)),this.registerTranslator(ee),this.editor.filters.set(new Y(this.editor,this.name)),this.editor.dialogs.set(new X(this.editor,this.name)),this.editor.commands.set(new Q(this.editor,this.name,"table"))}}class ie extends a{constructor(e){super(e,"text")}init(){this.registerTag({name:"strong",group:"text"}),this.registerTag({name:"i",group:"text"}),this.editor.commands.set(new d(this.editor,"bold","strong")),this.editor.converters.set(new e(this.editor,"b","strong")),this.editor.commands.set(new d(this.editor,"italic","i"))}}class ne extends d{insert({src:e,caption:t="",width:i="",height:n="",controls:r="controls"}={}){if(!e)throw"Invalid argument";const s=this.editor.createElement("figure",{attributes:{class:this.name}});s.appendChild(this.editor.createElement(this.tagName,{attributes:{src:this.editor.url(e),width:i,height:n,controls:r}})),s.appendChild(this.editor.createElement("figcaption",{content:t,html:!0})),this.editor.insert(s)}}class re extends i{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Video")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}const se={de:{Cancel:"Abbrechen",Height:"Höhe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Video:"Video",Width:"Breite"}};class ae extends a{constructor(e){super(e,"video")}init(){this.registerTag({name:"video",group:"media",attributes:["controls","height","src","width"],empty:!0}),this.registerTranslator(se),this.editor.config[this.name].browser?this.editor.dialogs.set(new m(this.editor,this.name,this.editor.config[this.name].browser)):this.editor.dialogs.set(new re(this.editor,this.name)),this.editor.commands.set(new ne(this.editor,this.name,"video"))}static defaultConfig(){return{browser:null}}}export default class extends l{static defaultConfig(){return{base:{plugins:[v,u,H,M,R,I,$,K,W,V,Z,J,te,ie,ae],toolbar:["fullscreen","bold","italic","link","paragraph","heading","subheading","unorderedlist","orderedlist","quote","image","video","audio","iframe","table","details","block"]}}}}
