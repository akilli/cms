class e{constructor(e,t){if(!(e instanceof d&&t&&"string"==typeof t))throw"Invalid argument";this.editor=e,this.name=t,this.element=this.editor.elements.get(this.name)||null,this.dialog=this.editor.dialogs.get(this.name)||null}execute(){this.dialog?this.dialog.open(e=>this.insert(e),this.oldData()):this.insert()}insert(e={}){this.element&&this.element.insert(e)}oldData(){const e={},t=this.editor.getSelectedElement();return t instanceof HTMLElement&&t.tagName.toLowerCase()===this.element.tagName&&Array.from(t.attributes).forEach(t=>e[t.nodeName]=t.nodeValue),e}}class t{constructor(e,t,i){if(!(e instanceof d&&t&&"string"==typeof t&&i&&"string"==typeof i))throw"Invalid argument";this.editor=e,this.name=t,this.target=i}convert(e){return this.editor.createElement(this.target,{},e.innerHTML)}}class i{constructor(e,t,i){if(!(e instanceof d&&t&&"string"==typeof t&&i&&"string"==typeof i))throw"Invalid argument";this.editor=e,this.name=t,this.tagName=i}create(e={}){return this.editor.createElement(this.tagName,e)}insert(e={}){this.editor.insert(this.create(e))}}class n{constructor(e,t){if(!(e instanceof d&&t&&"string"==typeof t))throw"Invalid argument";this.editor=e,this.name=t}filter(e){throw"Not implemented"}}class r{constructor(e){if(!(e instanceof d))throw"Invalid argument";this.editor=e}observe(e){throw"Not implemented"}}class s{constructor(e,t){if(!(e instanceof d&&t&&"string"==typeof t))throw"Invalid argument";this.config()&&(e.config[t]=Object.assign({},this.config(),e.config[t]||{})),this.editor=e,this.name=t}config(){return null}init(){throw"Not implemented"}}class o{constructor({name:e,group:t,attributes:i=[],children:n=[],editable:r=!1,empty:s=!1,enter:o=null}={}){if(!e||"string"!=typeof e||!t||"string"!=typeof t)throw"Invalid argument";this.name=e,this.group=t,this.children=Array.isArray(n)?n:[],this.attributes=Array.isArray(i)?i:[],this.editable=Boolean(r),this.empty=Boolean(s),this.enter=o&&"string"==typeof o?o:null}}class a{constructor(e,t){if(!e||"string"!=typeof e)throw"Invalid argument";this.name=e,this.data=t}get(e){return this.data[e]||e}}class l extends Map{constructor(e,t=[]){super(),this.type=e,t.forEach(e=>this.set(e))}set(e){if(!(e instanceof this.type&&e.hasOwnProperty("name")))throw"Invalid argument";super.set(e.name,e)}}class d{constructor(r,d={}){if(!(r instanceof HTMLElement))throw"No HTML element";for(let[e,t]of Object.entries(this.constructor.defaultConfig||{}))d[e]=Object.assign({},t,d[e]||{});this.orig=r,this.document=this.orig.ownerDocument,this.window=this.document.defaultView,this.origin=this.window.origin||this.window.location.origin,this.element=this.createElement("div",{class:"editor"}),this.content=this.createElement("div",{class:"editor-content"}),this.toolbar=this.createElement("div",{class:"editor-toolbar"}),this.orig.hidden=!0,this.orig.insertAdjacentElement("afterend",this.element),this.element.appendChild(this.toolbar),this.element.appendChild(this.content),this.config=d,this.translators=new l(a),this.tags=new l(o,this.config.base.tags.map(e=>new o(e))),this.elements=new l(i),this.converters=new l(t),this.filters=new l(n),this.dialogs=new l(h),this.commands=new l(e),this.plugins=new l(s,this.config.base.plugins.map(e=>new e(this)))}init(){this.plugins.forEach(e=>e.init()),this.initContent(),this.initToolbar()}initContent(){this.orig instanceof HTMLTextAreaElement?(this.content.innerHTML=this.orig.value.replace("/&nbsp;/g"," "),this.orig.form.addEventListener("submit",()=>this.save())):this.content.innerHTML=this.orig.innerHTML,this.filter(this.content)}initToolbar(){Array.isArray(this.config.base.toolbar)&&this.config.base.toolbar.forEach(e=>{if(!this.commands.has(e))throw"Invalid argument";const t=this.createElement("button",{type:"button","data-cmd":e,title:e},e);t.addEventListener("click",()=>{this.window.getSelection().containsNode(this.content,!0)||this.content.focus(),this.commands.get(e).execute()}),this.toolbar.appendChild(t)})}getData(){const e=this.content.cloneNode(!0);return this.filter(e),e.innerHTML}save(){this.orig instanceof HTMLTextAreaElement?this.orig.value=this.getData():this.orig.innerHTML=this.getData()}insert(e){let t;if(!(e instanceof HTMLElement&&(t=this.tags.get(e.tagName.toLowerCase()))))throw"Invalid HTML element";"text"===t.group?this.formatText(e):this.insertWidget(e)}insertWidget(e){if(!(e instanceof HTMLElement))throw"Invalid HTML element";if(!this.allowed(e.tagName.toLowerCase(),"root"))throw"Element is not allowed here";this.content.appendChild(e)}insertText(e){this.document.execCommand("inserttext",!1,e)}formatText(e){if(!(e instanceof HTMLElement))throw"Invalid HTML element";const t=this.window.getSelection(),i=t.anchorNode instanceof Text?t.anchorNode.parentElement:t.anchorNode,n=this.getSelectedEditable();if(t.isCollapsed||!t.toString().trim()||!(i instanceof HTMLElement)||!n)return;const r=t.getRangeAt(0),s=this.tags.get(i.tagName.toLowerCase()),o=s&&"text"!==s.group?i:i.parentElement;r.startContainer instanceof Text&&!r.startContainer.parentElement.isSameNode(o)&&r.setStartBefore(r.startContainer.parentElement),r.endContainer instanceof Text&&!r.endContainer.parentElement.isSameNode(o)&&r.setEndAfter(r.endContainer.parentElement);const a=r.toString(),l=r.cloneContents().childNodes;let d=Array.from(l).every(t=>t instanceof Text&&!t.textContent.trim()||t instanceof HTMLElement&&t.tagName===e.tagName);r.deleteContents(),!(o.isContentEditable&&this.allowed(e.tagName.toLowerCase(),o.tagName.toLowerCase())&&a.trim())||s&&d?r.insertNode(this.createText(a)):(e.textContent=a,r.insertNode(e)),o.normalize()}createElement(e,t={},i=""){const n=this.document.createElement(e);n.innerHTML=i;for(let[e,i]of Object.entries(t))i&&n.setAttribute(e,""+i);return n}createText(e){return this.document.createTextNode(e)}getSelectedElement(){const e=this.window.getSelection(),t=e.anchorNode instanceof Text?e.anchorNode.parentElement:e.anchorNode,i=e.focusNode instanceof Text?e.focusNode.parentElement:e.focusNode;return t instanceof HTMLElement&&i instanceof HTMLElement&&t.isSameNode(i)&&this.content.contains(t)?t:null}getSelectedEditable(){const e=this.window.getSelection(),t=e.anchorNode instanceof Text?e.anchorNode.parentElement:e.anchorNode,i=e.focusNode instanceof Text?e.focusNode.parentElement:e.focusNode;if(t instanceof HTMLElement&&i instanceof HTMLElement){const e=t.closest("[contenteditable=true]"),n=i.closest("[contenteditable=true]");if(e instanceof HTMLElement&&n instanceof HTMLElement&&e.isSameNode(n)&&this.content.contains(e))return e}return null}getSelectedWidget(){const e=this.getSelectedElement();return e?e.closest("div.editor-content > *"):null}focusEnd(e){if(!(e instanceof HTMLElement))throw"No HTML element";const t=this.document.createRange(),i=this.window.getSelection();e.focus(),t.selectNodeContents(e),t.collapse(),i.removeAllRanges(),i.addRange(t)}observe(e,t={childList:!0,subtree:!0}){if(!(e instanceof r))throw"Invalid argument";new MutationObserver(t=>e.observe(t)).observe(this.content,t)}filter(e){if(!(e instanceof HTMLElement))throw"No HTML element";this.filters.forEach(t=>{e.normalize(),t.filter(e)})}convert(e){if(!(e instanceof HTMLElement))throw"No HTML element";const t=this.converters.get(e.tagName.toLowerCase());if(!t)return e;const i=t.convert(e);return e.parentElement.replaceChild(i,e),i}isRoot(e){if(!(e instanceof HTMLElement))throw"No HTML element";return this.content.isSameNode(e)||"editor-content"===e.getAttribute("class")}getTagName(e){return this.isRoot(e)?"root":e.tagName.toLowerCase()}allowed(e,t){const i=this.tags.get(e),n=i?i.group:e,r=this.tags.get(t);return r&&r.children.includes(n)}url(e){const t=this.createElement("a",{href:e});return t.origin===this.origin?t.pathname:t.href}static create(e,t={}){const i=new this(e,t);return i.init(),i}}class h{constructor(e,t){if(!(e instanceof d&&t&&"string"==typeof t))throw"Invalid argument";this.editor=e,this.name=t,this.translator=this.editor.translators.get(this.name)||new a(this.name,{})}open(e,t={}){this.editor.document.querySelectorAll("dialog.editor-dialog").forEach(e=>e.parentElement.removeChild(e));const i=this.editor.window.getSelection(),n=i.rangeCount>0?i.getRangeAt(0):null,r=this.editor.createElement("dialog",{class:"editor-dialog"}),s=this.editor.createElement("form"),o=this.editor.createElement("fieldset"),a=this.editor.createElement("button",{type:"button","data-action":"cancel"},this.translator.get("Cancel")),l=this.editor.createElement("button",{"data-action":"save"},this.translator.get("Save")),d=()=>{n&&(i.removeAllRanges(),i.addRange(n)),r.parentElement.removeChild(r)};r.appendChild(s),r.addEventListener("click",e=>{e.target===r&&d()}),"boolean"!=typeof r.open&&this.polyfill(r),r.open=!0,o.insertAdjacentHTML("beforeend",this.getFieldsetHtml());for(let[e,i]of Object.entries(t))o.elements[e]&&(o.elements[e].value=i);s.appendChild(o),s.addEventListener("submit",t=>{t.preventDefault(),d();const i={};Array.from(o.elements).forEach(e=>i[e.name]=e.value),e(i)}),a.addEventListener("click",d),s.appendChild(a),s.appendChild(l),this.editor.element.appendChild(r)}getFieldsetHtml(){throw"Not implemented"}polyfill(e){Object.defineProperty(e,"open",{get:function(){return this.hasAttribute("open")},set:function(e){e?this.setAttribute("open",""):this.removeAttribute("open")}})}}class c extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Audio")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class m extends i{constructor(e){super(e,"audio","audio")}create({caption:e="",...t}={}){if(!t.src)throw"No media element";t.src=this.editor.url(t.src),t.controls="controls";const i=this.editor.createElement("figure",{class:"audio"}),n=this.editor.createElement("audio",t),r=this.editor.createElement("figcaption",{},e);return i.appendChild(n),i.appendChild(r),i}}class u extends h{constructor(e,t){if(super(e,t),!this.editor.config[this.name].browserUrl)throw"Invalid argument";this.url=this.editor.config[this.name].browserUrl,this.opts=Object.assign({alwaysRaised:"yes",dependent:"yes",height:""+this.editor.window.screen.height,location:"no",menubar:"no",minimizable:"no",modal:"yes",resizable:"yes",scrollbars:"yes",toolbar:"no",width:""+this.editor.window.screen.width},this.editor.config[this.name].browserOpts)}open(e,t={}){const i=Object.entries(this.opts).map(e=>`${e[0]}=${e[1]}`).join(","),n=this.editor.window.open(this.url,this.name,i),r=this.editor.createElement("a",{href:this.url});this.editor.window.addEventListener("message",t=>{t.origin===r.origin&&t.source===n&&(e(t.data),n.close())},!1)}}const g={de:{Audio:"Audio",Cancel:"Abbrechen",Height:"Höhe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Width:"Breite"}};class p extends n{filter(e){const t=this.editor.getTagName(e),i=this.editor.isRoot(e);Array.from(e.childNodes).forEach(n=>{if(n instanceof HTMLElement&&(n=this.editor.convert(n)),n instanceof HTMLElement){const r=n.tagName.toLowerCase(),s=this.editor.tags.get(r),o=n.textContent.trim();s&&(this.editor.allowed(r,t)||i&&"text"===s.group&&this.editor.allowed("p",t))?(Array.from(n.attributes).forEach(e=>{s.attributes.includes(e.name)||n.removeAttribute(e.name)}),n.hasChildNodes()&&this.editor.filter(n),n.hasChildNodes()||s.empty?this.editor.allowed(r,t)||e.replaceChild(this.editor.createElement("p",{},n.outerHTML),n):e.removeChild(n)):i&&o&&this.editor.allowed("p",t)?e.replaceChild(this.editor.createElement("p",{},o),n):o&&this.editor.allowed("text",t)?e.replaceChild(this.editor.createText(o),n):e.removeChild(n)}else if(n instanceof Text){const r=n.textContent.trim();i&&r&&this.editor.allowed("p",t)?e.replaceChild(this.editor.createElement("p",{},r),n):r&&this.editor.allowed("text",t)||e.removeChild(n)}else e.removeChild(n)}),e.innerHTML=e.innerHTML.replace(/^\s*(<br\s*\/?>\s*)+/gi,"").replace(/\s*(<br\s*\/?>\s*)+$/gi,""),e instanceof HTMLParagraphElement?e.outerHTML=e.outerHTML.replace(/\s*(<br\s*\/?>\s*){2,}/gi,"</p><p>"):e.innerHTML=e.innerHTML.replace(/\s*(<br\s*\/?>\s*){2,}/gi,"<br>")}}class f extends r{constructor(e){super(e),this.editables=[...this.editor.tags].reduce((e,t)=>(t[1].editable&&e.push(t[1].name),e),[])}observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&this.editables.includes(e.tagName.toLowerCase())?this.toEditable(e):e instanceof HTMLElement&&e.querySelectorAll(this.editables.join(", ")).forEach(e=>this.toEditable(e))}))}toEditable(e){e.contentEditable="true",e.focus(),e.addEventListener("keydown",e=>{this.onKeydownEnter(e),this.onKeydownBackspace(e)}),e.addEventListener("keyup",e=>this.onKeyupEnter(e))}onKeydownEnter(e){"Enter"!==e.key||e.shiftKey&&this.editor.allowed("br",e.target.tagName.toLowerCase())||(e.preventDefault(),e.cancelBubble=!0)}onKeyupEnter(e){let t;if("Enter"===e.key&&!e.shiftKey&&(t=this.editor.tags.get(e.target.tagName.toLowerCase()))&&t.enter){let i,n=e.target;e.preventDefault(),e.cancelBubble=!0;do{if(i=this.editor.getTagName(n.parentElement),this.editor.allowed(t.enter,i)){n.insertAdjacentElement("afterend",this.editor.createElement(t.enter));break}}while((n=n.parentElement)&&this.editor.content.contains(n)&&!this.editor.isRoot(n))}}onKeydownBackspace(e){const t=this.editor.getSelectedWidget(),i=["blockquote","details","ol","ul"].includes(e.target.parentElement.tagName.toLowerCase())&&("summary"!==e.target.tagName.toLowerCase()||e.target.matches(":only-child"));if("Backspace"===e.key&&!e.shiftKey&&!e.target.textContent&&t&&(t.isSameNode(e.target)||i)){const i=t.isSameNode(e.target)||e.target.matches(":only-child")?t:e.target;i.previousElementSibling&&this.editor.focusEnd(i.previousElementSibling),i.parentElement.removeChild(i),e.preventDefault(),e.cancelBubble=!0}}}class b extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&e.parentElement instanceof HTMLElement&&this.editor.isRoot(e.parentElement)&&(e.tabIndex=0,this.keyboard(e),this.dragndrop(e))}))}keyboard(e){e.addEventListener("keyup",t=>{this.editor.document.activeElement.isSameNode(e)&&("Delete"!==t.key||e.isContentEditable?e.draggable&&"ArrowUp"===t.key&&e.previousElementSibling?(e.previousElementSibling.insertAdjacentHTML("beforebegin",e.outerHTML),e.parentElement.removeChild(e),t.preventDefault(),t.cancelBubble=!0):e.draggable&&"ArrowDown"===t.key&&e.nextElementSibling&&(e.nextElementSibling.insertAdjacentHTML("afterend",e.outerHTML),e.parentElement.removeChild(e),t.preventDefault(),t.cancelBubble=!0):(e.parentElement.removeChild(e),t.preventDefault(),t.cancelBubble=!0))})}dragndrop(e){const t="text/x-editor-name",i=()=>{const t=e.hasAttribute("draggable");this.editor.content.querySelectorAll("[draggable]").forEach(e=>{e.removeAttribute("draggable"),e.hasAttribute("contenteditable")&&e.setAttribute("contenteditable","true")}),t||(e.setAttribute("draggable","true"),e.hasAttribute("contenteditable")&&e.setAttribute("contenteditable","false"))},n=i=>{const n=i.dataTransfer.getData(t);n&&this.editor.allowed(n,"root")&&(i.preventDefault(),e.setAttribute("data-dragover",""),i.dataTransfer.dropEffect="move")};e.addEventListener("dblclick",i),e.addEventListener("dragstart",i=>{i.dataTransfer.effectAllowed="move",i.dataTransfer.setData(t,e.tagName.toLowerCase()),i.dataTransfer.setData("text/x-editor-html",e.outerHTML)}),e.addEventListener("dragend",t=>{"move"===t.dataTransfer.dropEffect&&e.parentElement.removeChild(e),i()}),e.addEventListener("dragenter",n),e.addEventListener("dragover",n),e.addEventListener("dragleave",()=>e.removeAttribute("data-dragover")),e.addEventListener("drop",i=>{const n=i.dataTransfer.getData(t),r=i.dataTransfer.getData("text/x-editor-html");i.preventDefault(),e.removeAttribute("data-dragover"),n&&this.editor.allowed(n,"root")&&r&&e.insertAdjacentHTML("beforebegin",r)})}}class w extends i{constructor(e){super(e,"blockquote","blockquote")}create({caption:e=""}={}){const t=this.editor.createElement("figure",{class:"quote"}),i=this.editor.createElement("blockquote"),n=this.editor.createElement("figcaption",{},e);return i.appendChild(this.editor.createElement("p")),t.appendChild(i),t.appendChild(n),t}}class E extends i{constructor(e){super(e,"details","details")}create(e={}){const t=this.editor.createElement("details");return t.appendChild(this.editor.createElement("summary")),t.appendChild(this.editor.createElement("p")),t}}class v extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLDetailsElement?this.initDetails(e):e instanceof HTMLElement&&"summary"===e.tagName.toLowerCase()?this.initSummary(e):e instanceof HTMLElement&&e.querySelectorAll("details").forEach(e=>this.initDetails(e))}))}initDetails(e){let t=e.firstElementChild;if(t instanceof HTMLElement&&"summary"===t.tagName.toLowerCase())this.initSummary(t),1===e.childElementCount&&e.appendChild(this.editor.createElement("p"));else if(t instanceof HTMLElement){const i=this.editor.createElement("summary");e.insertBefore(i,t),this.initSummary(i)}}initSummary(e){const t=()=>{e.textContent.trim()||(e.textContent=this.editor.translators.get("details").get("Details"))};t(),e.addEventListener("blur",t),e.addEventListener("keydown",e=>{" "===e.key&&(e.preventDefault(),e.cancelBubble=!0)}),e.addEventListener("keyup",e=>{" "===e.key&&(e.preventDefault(),e.cancelBubble=!0,this.editor.insertText(" "))})}}const L={de:{Details:"Details"}};class y extends n{filter(e){e.querySelectorAll("figure > figcaption:only-child").forEach(e=>e.parentElement.removeChild(e))}}class T extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLElement&&"figure"===e.tagName.toLowerCase()&&!e.querySelector(":scope > figcaption")&&e.appendChild(this.editor.createElement("figcaption"))}))}}class x extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Iframe")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class C extends i{constructor(e){super(e,"iframe","iframe")}create({caption:e="",...t}={}){if(!t.src)throw"No media element";t.src=this.editor.url(t.src),t.allowfullscreen="allowfullscreen";const i=this.editor.createElement("figure",{class:"iframe"}),n=this.editor.createElement("iframe",t),r=this.editor.createElement("figcaption",{},e);return i.appendChild(n),i.appendChild(r),i}}const H={de:{Cancel:"Abbrechen",Height:"Höhe",Ifrane:"Iframe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Width:"Breite"}};class A extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Image")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="alt">\n                <label for="editor-alt">${this.translator.get("Alternative text")}</label>\n                <input id="editor-alt" name="alt" type="text" placeholder="${this.translator.get("Replacement text for use when media elements are not available")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class M extends i{constructor(e){super(e,"image","img")}create({caption:e="",...t}={}){if(!t.src)throw"No media element";t.src=this.editor.url(t.src);const i=this.editor.createElement("figure",{class:"image"}),n=this.editor.createElement("img",t),r=this.editor.createElement("figcaption",{},e);return i.appendChild(n),i.appendChild(r),i}}const N={de:{"Alternative text":"Alternativtext",Cancel:"Abbrechen",Height:"Höhe",Image:"Bild","Insert URL to media element":"URL zum Medienelement eingeben","Replacement text for use when media elements are not available":"Ersatztext, falls Medienelemente nicht verfügbar sind",Save:"Speichern",URL:"URL",Width:"Breite"}};class k extends e{constructor(e){super(e,"link")}insert({href:e=null}={}){const t=this.editor.getSelectedElement(),i=t instanceof HTMLAnchorElement?t:null;e&&i?i.setAttribute("href",this.editor.url(e)):e?this.element.insert({href:this.editor.url(e)}):i&&i.parentElement.replaceChild(this.editor.createText(i.textContent),i)}}class S extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Link")}</legend>\n            <div data-attr="href">\n                <label for="editor-href">${this.translator.get("URL")}</label>\n                <input id="editor-href" name="href" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to add a link or leave empty to unlink")}" />\n            </div>\n        `}}const R={de:{Cancel:"Abbrechen","Insert URL to add a link or leave empty to unlink":"URL eingeben um zu verlinken oder leer lassen um Link zu entfernen",Link:"Link",Save:"Speichern",URL:"URL"}};class U extends i{constructor(e){super(e,"orderedlist","ol")}create(e={}){const t=this.editor.createElement("ol");return t.appendChild(this.editor.createElement("li")),t}}class I extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Table")}</legend>\n            <div data-attr="rows" data-required>\n                <label for="editor-rows">${this.translator.get("Rows")}</label>\n                <input id="editor-rows" name="rows" type="number" value="1" required="required" min="1" />\n            </div>\n            <div data-attr="cols" data-required>\n                <label for="editor-cols">${this.translator.get("Columns")}</label>\n                <input id="editor-cols" name="cols" type="number" value="1" required="required" min="1" />\n            </div>\n        `}}class D extends i{constructor(e){super(e,"table","table")}create({rows:e=1,cols:t=1,caption:i=""}={}){if(e<=0||t<=0)throw"Invalid argument";const n=this.editor.createElement("figure",{class:"table"}),r=this.editor.createElement("table"),s=this.editor.createElement("figcaption",{},i);return n.appendChild(r),n.appendChild(s),["thead","tbody","tfoot"].forEach(i=>{const n=this.editor.createElement(i),s="thead"===i?"th":"td",o="tbody"===i?e:1;let a;r.appendChild(n);for(let e=0;e<o;e++){a=this.editor.createElement("tr"),n.appendChild(a);for(let e=0;e<t;++e)a.appendChild(this.editor.createElement(s))}}),n}}class $ extends n{filter(e){if(e instanceof HTMLTableRowElement&&!e.querySelector("th:not(:empty), td:not(:empty)")||e instanceof HTMLTableSectionElement&&e.rows.length<=0||e instanceof HTMLTableElement&&e.querySelector("thead, tfoot")&&!e.querySelector("tbody"))for(;e.firstChild;)e.removeChild(e.firstChild)}}class q extends r{observe(e){e.forEach(e=>e.addedNodes.forEach(e=>{e instanceof HTMLTableElement?this.initTable(e):e instanceof HTMLElement&&e.querySelectorAll("table").forEach(e=>this.initTable(e))}))}initTable(e){this.sections(e),this.keyboard(e)}sections(e){if(e.tBodies.length>0&&e.tBodies[0].rows[0]&&(!e.tHead||!e.tFoot)){const t=e.tBodies[0].rows[0].cells.length;let i;if(!e.tHead){i=e.createTHead().insertRow();for(let e=0;e<t;e++)i.appendChild(this.editor.createElement("th"))}if(!e.tFoot){i=e.createTFoot().insertRow();for(let e=0;e<t;e++)i.insertCell()}}}keyboard(e){e.addEventListener("keydown",t=>{const i=t.target,n=i.parentElement,r=n.parentElement;if(i instanceof HTMLTableCellElement&&n instanceof HTMLTableRowElement&&(r instanceof HTMLTableElement||r instanceof HTMLTableSectionElement)&&(t.altKey||t.ctrlKey)&&["ArrowLeft","ArrowRight","ArrowUp","ArrowDown"].includes(t.key)){const s=n.cells.length,o=r.rows.length,a=r instanceof HTMLTableElement?n.rowIndex:n.sectionRowIndex;let l;if(t.altKey&&("ArrowLeft"===t.key&&i.cellIndex>0||"ArrowRight"===t.key&&i.cellIndex<s-1))l=i.cellIndex+("ArrowLeft"===t.key?-1:1),Array.from(e.rows).forEach(e=>e.deleteCell(l));else if(t.altKey&&("ArrowUp"===t.key&&a>0||"ArrowDown"===t.key&&a<o-1))l=a+("ArrowUp"===t.key?-1:1),r.deleteRow(l);else if(!t.ctrlKey||"ArrowLeft"!==t.key&&"ArrowRight"!==t.key){if(t.ctrlKey&&("ArrowUp"===t.key||"ArrowDown"===t.key)){l=a+("ArrowUp"===t.key?0:1);const e=r.insertRow(l);for(let t=0;t<s;t++)e.insertCell()}}else l=i.cellIndex+("ArrowLeft"===t.key?0:1),Array.from(e.rows).forEach(e=>{e.querySelector(":scope > td")?e.insertCell(l):e.insertBefore(this.editor.createElement("th"),e.cells[l])});t.preventDefault(),t.cancelBubble=!0}})}}const B={de:{Cancel:"Abbrechen",Columns:"Spalten",Rows:"Zeilen",Save:"Speichern",Table:"Tabelle"}};class j extends i{constructor(e){super(e,"unorderedlist","ul")}create(e={}){const t=this.editor.createElement("ul");return t.appendChild(this.editor.createElement("li")),t}}class K extends h{getFieldsetHtml(){return`\n            <legend>${this.translator.get("Video")}</legend>\n            <div data-attr="src">\n                <label for="editor-src">${this.translator.get("URL")}</label>\n                <input id="editor-src" name="src" type="text" pattern="(https?|/).+" placeholder="${this.translator.get("Insert URL to media element")}" />\n            </div>\n            <div data-attr="width">\n                <label for="editor-width">${this.translator.get("Width")}</label>\n                <input id="editor-width" name="width" type="number" />\n            </div>\n            <div data-attr="height">\n                <label for="editor-height">${this.translator.get("Height")}</label>\n                <input id="editor-height" name="height" type="number" />\n            </div>\n        `}}class O extends i{constructor(e){super(e,"video","video")}create({caption:e="",...t}={}){if(!t.src)throw"No media element";t.src=this.editor.url(t.src),t.controls="controls";const i=this.editor.createElement("figure",{class:"video"}),n=this.editor.createElement("video",t),r=this.editor.createElement("figcaption",{},e);return i.appendChild(n),i.appendChild(r),i}}const W={de:{Cancel:"Abbrechen",Height:"Höhe","Insert URL to media element":"URL zum Medienelement eingeben",Save:"Speichern",URL:"URL",Video:"Video",Width:"Breite"}};class z extends d{}z.defaultConfig={base:{plugins:[class extends s{constructor(e){super(e,"base")}config(){return{lang:null,plugins:[],tags:[],toolbar:[]}}init(){this.editor.observe(new f(this.editor)),this.editor.observe(new b(this.editor)),this.editor.filters.set(new p(this.editor,this.name))}},class extends s{constructor(e){super(e,"audio")}config(){return{browserUrl:null,browserOpts:{}}}init(){this.editor.translators.set(new a(this.name,g[this.editor.config.base.lang]||{})),this.editor.elements.set(new m(this.editor));const t=this.editor.config[this.name].browserUrl?u:c;this.editor.dialogs.set(new t(this.editor,this.name)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"blockquote")}init(){this.editor.elements.set(new w(this.editor)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"bold")}init(){this.editor.elements.set(new i(this.editor,this.name,"strong")),this.editor.commands.set(new e(this.editor,this.name)),this.editor.converters.set(new t(this.editor,"b","strong"))}},class extends s{constructor(e){super(e,"details")}init(){this.editor.observe(new v(this.editor)),this.editor.translators.set(new a(this.name,L[this.editor.config.base.lang]||{})),this.editor.elements.set(new E(this.editor)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"figure")}init(){this.editor.observe(new T(this.editor)),this.editor.filters.set(new y(this.editor,this.name))}},class extends s{constructor(e){super(e,"heading")}init(){this.editor.elements.set(new i(this.editor,this.name,"h2")),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"iframe")}config(){return{browserUrl:null,browserOpts:{}}}init(){this.editor.translators.set(new a(this.name,H[this.editor.config.base.lang]||{})),this.editor.elements.set(new C(this.editor));const t=this.editor.config[this.name].browserUrl?u:x;this.editor.dialogs.set(new t(this.editor,this.name)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"image")}config(){return{browserUrl:null,browserOpts:{}}}init(){this.editor.translators.set(new a(this.name,N[this.editor.config.base.lang]||{})),this.editor.elements.set(new M(this.editor));const t=this.editor.config[this.name].browserUrl?u:A;this.editor.dialogs.set(new t(this.editor,this.name)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"italic")}init(){this.editor.elements.set(new i(this.editor,this.name,"i")),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"link")}init(){this.editor.translators.set(new a(this.name,R[this.editor.config.base.lang]||{})),this.editor.elements.set(new i(this.editor,this.name,"a")),this.editor.dialogs.set(new S(this.editor,this.name)),this.editor.commands.set(new k(this.editor))}},class extends s{constructor(e){super(e,"orderedlist")}init(){this.editor.elements.set(new U(this.editor)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"paragraph")}init(){this.editor.elements.set(new i(this.editor,this.name,"p")),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"subheading")}init(){this.editor.elements.set(new i(this.editor,this.name,"h3")),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"table")}init(){this.editor.observe(new q(this.editor)),this.editor.translators.set(new a(this.name,B[this.editor.config.base.lang]||{})),this.editor.elements.set(new D(this.editor)),this.editor.filters.set(new $(this.editor,this.name)),this.editor.dialogs.set(new I(this.editor,this.name)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"unorderedlist")}init(){this.editor.elements.set(new j(this.editor)),this.editor.commands.set(new e(this.editor,this.name))}},class extends s{constructor(e){super(e,"video")}config(){return{browserUrl:null,browserOpts:{}}}init(){this.editor.translators.set(new a(this.name,W[this.editor.config.base.lang]||{})),this.editor.elements.set(new O(this.editor));const t=this.editor.config[this.name].browserUrl?u:K;this.editor.dialogs.set(new t(this.editor,this.name)),this.editor.commands.set(new e(this.editor,this.name))}}],tags:[{name:"root",group:"root",children:["details","figure","heading","list","paragraph"]},{name:"p",group:"paragraph",children:["break","text"],editable:!0,enter:"p"},{name:"h2",group:"heading",editable:!0,enter:"p"},{name:"h3",group:"heading",editable:!0,enter:"p"},{name:"ul",group:"list",children:["listitem"]},{name:"ol",group:"list",children:["listitem"]},{name:"li",group:"listitem",children:["break","text"],editable:!0,enter:"li"},{name:"figure",group:"figure",attributes:["class"],children:["blockquote","caption","media","table"]},{name:"figcaption",group:"caption",children:["text"],editable:!0,enter:"p"},{name:"blockquote",group:"blockquote",children:["paragraph"]},{name:"img",group:"media",attributes:["alt","height","src","width"],empty:!0},{name:"video",group:"media",attributes:["controls","height","src","width"],empty:!0},{name:"audio",group:"media",attributes:["controls","height","src","width"],empty:!0},{name:"iframe",group:"media",attributes:["allowfullscreen","height","src","width"],empty:!0},{name:"table",group:"table",children:["tablesection"]},{name:"thead",group:"tablesection",children:["tablerow"]},{name:"tbody",group:"tablesection",children:["tablerow"]},{name:"tfoot",group:"tablesection",children:["tablerow"]},{name:"tr",group:"tablerow",children:["tablecell"]},{name:"th",group:"tablecell",children:["break","text"],editable:!0,empty:!0},{name:"td",group:"tablecell",children:["break","text"],editable:!0,empty:!0},{name:"details",group:"details",children:["figure","list","paragraph","summary"]},{name:"summary",group:"summary",editable:!0,enter:"p"},{name:"strong",group:"text"},{name:"i",group:"text"},{name:"a",group:"text",attributes:["href"]},{name:"br",group:"break",empty:!0}],toolbar:["bold","italic","link","paragraph","heading","subheading","unorderedlist","orderedlist","blockquote","image","video","audio","iframe","table","details"]}};export default z;
