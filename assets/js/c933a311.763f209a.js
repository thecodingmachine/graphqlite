"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[6151],{19365:(e,t,n)=>{n.d(t,{A:()=>r});var a=n(96540),o=n(20053);const i={tabItem:"tabItem_Ymn6"};function r(e){let{children:t,hidden:n,className:r}=e;return a.createElement("div",{role:"tabpanel",className:(0,o.A)(i.tabItem,r),hidden:n},t)}},11470:(e,t,n)=>{n.d(t,{A:()=>N});var a=n(58168),o=n(96540),i=n(20053),r=n(23104),l=n(56347),s=n(57485),u=n(31682),c=n(89466);function d(e){return function(e){return o.Children.map(e,(e=>{if(!e||(0,o.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:a,default:o}}=e;return{value:t,label:n,attributes:a,default:o}}))}function p(e){const{values:t,children:n}=e;return(0,o.useMemo)((()=>{const e=t??d(n);return function(e){const t=(0,u.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function y(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function h(e){let{queryString:t=!1,groupId:n}=e;const a=(0,l.W6)(),i=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,s.aZ)(i),(0,o.useCallback)((e=>{if(!i)return;const t=new URLSearchParams(a.location.search);t.set(i,e),a.replace({...a.location,search:t.toString()})}),[i,a])]}function m(e){const{defaultValue:t,queryString:n=!1,groupId:a}=e,i=p(e),[r,l]=(0,o.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!y({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const a=n.find((e=>e.default))??n[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:t,tabValues:i}))),[s,u]=h({queryString:n,groupId:a}),[d,m]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[a,i]=(0,c.Dv)(n);return[a,(0,o.useCallback)((e=>{n&&i.set(e)}),[n,i])]}({groupId:a}),g=(()=>{const e=s??d;return y({value:e,tabValues:i})?e:null})();(0,o.useLayoutEffect)((()=>{g&&l(g)}),[g]);return{selectedValue:r,selectValue:(0,o.useCallback)((e=>{if(!y({value:e,tabValues:i}))throw new Error(`Can't select invalid tab value=${e}`);l(e),u(e),m(e)}),[u,m,i]),tabValues:i}}var g=n(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:n,selectedValue:l,selectValue:s,tabValues:u}=e;const c=[],{blockElementScrollPositionUntilNextRender:d}=(0,r.a_)(),p=e=>{const t=e.currentTarget,n=c.indexOf(t),a=u[n].value;a!==l&&(d(t),s(a))},y=e=>{let t=null;switch(e.key){case"Enter":p(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return o.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,i.A)("tabs",{"tabs--block":n},t)},u.map((e=>{let{value:t,label:n,attributes:r}=e;return o.createElement("li",(0,a.A)({role:"tab",tabIndex:l===t?0:-1,"aria-selected":l===t,key:t,ref:e=>c.push(e),onKeyDown:y,onClick:p},r,{className:(0,i.A)("tabs__item",f.tabItem,r?.className,{"tabs__item--active":l===t})}),n??t)})))}function v(e){let{lazy:t,children:n,selectedValue:a}=e;const i=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=i.find((e=>e.props.value===a));return e?(0,o.cloneElement)(e,{className:"margin-top--md"}):null}return o.createElement("div",{className:"margin-top--md"},i.map(((e,t)=>(0,o.cloneElement)(e,{key:t,hidden:e.props.value!==a}))))}function w(e){const t=m(e);return o.createElement("div",{className:(0,i.A)("tabs-container",f.tabList)},o.createElement(b,(0,a.A)({},e,t)),o.createElement(v,(0,a.A)({},e,t)))}function N(e){const t=(0,g.A)();return o.createElement(w,(0,a.A)({key:String(t)},e))}},39454:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>s,contentTitle:()=>r,default:()=>p,frontMatter:()=>i,metadata:()=>l,toc:()=>u});var a=n(58168),o=(n(96540),n(15680));n(67443),n(11470),n(19365);const i={id:"external-type-declaration",title:"External type declaration",sidebar_label:"External type declaration"},r=void 0,l={unversionedId:"external-type-declaration",id:"version-6.1/external-type-declaration",title:"External type declaration",description:"In some cases, you cannot or do not want to put an annotation on a domain class.",source:"@site/versioned_docs/version-6.1/external-type-declaration.mdx",sourceDirName:".",slug:"/external-type-declaration",permalink:"/docs/6.1/external-type-declaration",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.1/external-type-declaration.mdx",tags:[],version:"6.1",lastUpdatedBy:"Oleksandr Prypkhan",lastUpdatedAt:1727148156,formattedLastUpdatedAt:"Sep 24, 2024",frontMatter:{id:"external-type-declaration",title:"External type declaration",sidebar_label:"External type declaration"},sidebar:"docs",previous:{title:"Extending a type",permalink:"/docs/6.1/extend-type"},next:{title:"Input types",permalink:"/docs/6.1/input-types"}},s={},u=[{value:"<code>@Type</code> annotation with the <code>class</code> attribute",id:"type-annotation-with-the-class-attribute",level:2},{value:"<code>@SourceField</code> annotation",id:"sourcefield-annotation",level:2},{value:"<code>@MagicField</code> annotation",id:"magicfield-annotation",level:2},{value:"Authentication and authorization",id:"authentication-and-authorization",level:3},{value:"Declaring fields dynamically (without annotations)",id:"declaring-fields-dynamically-without-annotations",level:2}],c={toc:u},d="wrapper";function p(e){let{components:t,...n}=e;return(0,o.yg)(d,(0,a.A)({},c,n,{components:t,mdxType:"MDXLayout"}),(0,o.yg)("p",null,"In some cases, you cannot or do not want to put an annotation on a domain class."),(0,o.yg)("p",null,"For instance:"),(0,o.yg)("ul",null,(0,o.yg)("li",{parentName:"ul"},"The class you want to annotate is part of a third party library and you cannot modify it"),(0,o.yg)("li",{parentName:"ul"},"You are doing domain-driven design and don't want to clutter your domain object with annotations from the view layer"),(0,o.yg)("li",{parentName:"ul"},"etc.")),(0,o.yg)("h2",{id:"type-annotation-with-the-class-attribute"},(0,o.yg)("inlineCode",{parentName:"h2"},"@Type")," annotation with the ",(0,o.yg)("inlineCode",{parentName:"h2"},"class")," attribute"),(0,o.yg)("p",null,"GraphQLite allows you to use a ",(0,o.yg)("em",{parentName:"p"},"proxy")," class thanks to the ",(0,o.yg)("inlineCode",{parentName:"p"},"@Type")," annotation with the ",(0,o.yg)("inlineCode",{parentName:"p"},"class")," attribute:"),(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Types;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse App\\Entities\\Product;\n\n#[Type(class: Product::class)]\nclass ProductType\n{\n    #[Field]\n    public function getId(Product $product): string\n    {\n        return $product->getId();\n    }\n}\n")),(0,o.yg)("p",null,"The ",(0,o.yg)("inlineCode",{parentName:"p"},"ProductType")," class must be in the ",(0,o.yg)("em",{parentName:"p"},"types")," namespace. You configured this namespace when you installed GraphQLite."),(0,o.yg)("p",null,"The ",(0,o.yg)("inlineCode",{parentName:"p"},"ProductType")," class is actually a ",(0,o.yg)("strong",{parentName:"p"},"service"),". You can therefore inject dependencies in it."),(0,o.yg)("div",{class:"alert alert--warning"},(0,o.yg)("strong",null,"Heads up!")," The ",(0,o.yg)("code",null,"ProductType")," class must exist in the container of your application and the container identifier MUST be the fully qualified class name.",(0,o.yg)("br",null),(0,o.yg)("br",null),"If you are using the Symfony bundle (or a framework with autowiring like Laravel), this is usually not an issue as the container will automatically create the controller entry if you do not explicitly declare it."),(0,o.yg)("p",null,"In methods with a ",(0,o.yg)("inlineCode",{parentName:"p"},"@Field")," annotation, the first parameter is the ",(0,o.yg)("em",{parentName:"p"},"resolved object")," we are working on. Any additional parameters are used as arguments."),(0,o.yg)("h2",{id:"sourcefield-annotation"},(0,o.yg)("inlineCode",{parentName:"h2"},"@SourceField")," annotation"),(0,o.yg)("p",null,"If you don't want to rewrite all ",(0,o.yg)("em",{parentName:"p"},"getters")," of your base class, you may use the ",(0,o.yg)("inlineCode",{parentName:"p"},"@SourceField")," annotation:"),(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n#[Type(class: Product::class)]\n#[SourceField(name: "name")]\n#[SourceField(name: "price")]\nclass ProductType\n{\n}\n')),(0,o.yg)("p",null,"By doing so, you let GraphQLite know that the type exposes the ",(0,o.yg)("inlineCode",{parentName:"p"},"getName")," method of the underlying ",(0,o.yg)("inlineCode",{parentName:"p"},"Product")," object."),(0,o.yg)("p",null,"Internally, GraphQLite will look for methods named ",(0,o.yg)("inlineCode",{parentName:"p"},"name()"),", ",(0,o.yg)("inlineCode",{parentName:"p"},"getName()")," and ",(0,o.yg)("inlineCode",{parentName:"p"},"isName()"),").\nYou can set different name to look for with ",(0,o.yg)("inlineCode",{parentName:"p"},"sourceName")," attribute."),(0,o.yg)("h2",{id:"magicfield-annotation"},(0,o.yg)("inlineCode",{parentName:"h2"},"@MagicField")," annotation"),(0,o.yg)("p",null,"If your object has no getters, but instead uses magic properties (using the magic ",(0,o.yg)("inlineCode",{parentName:"p"},"__get")," method), you should use the ",(0,o.yg)("inlineCode",{parentName:"p"},"@MagicField")," annotation:"),(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse App\\Entities\\Product;\n\n#[Type]\n#[MagicField(name: "name", outputType: "String!")]\n#[MagicField(name: "price", outputType: "Float")]\nclass ProductType\n{\n    public function __get(string $property) {\n        // return some magic property\n    }\n}\n')),(0,o.yg)("p",null,'By doing so, you let GraphQLite know that the type exposes "name" and the "price" magic properties of the underlying ',(0,o.yg)("inlineCode",{parentName:"p"},"Product")," object.\nYou can set different name to look for with ",(0,o.yg)("inlineCode",{parentName:"p"},"sourceName")," attribute."),(0,o.yg)("p",null,"This is particularly useful in frameworks like Laravel, where Eloquent is making a very wide use of such properties."),(0,o.yg)("p",null,"Please note that GraphQLite has no way to know the type of a magic property. Therefore, you have specify the GraphQL type\nof each property manually."),(0,o.yg)("h3",{id:"authentication-and-authorization"},"Authentication and authorization"),(0,o.yg)("p",null,'You may also check for logged users or users with a specific right using the "annotations" property.'),(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\Type;\nuse TheCodingMachine\\GraphQLite\\Annotations\\SourceField;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Logged;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Right;\nuse TheCodingMachine\\GraphQLite\\Annotations\\FailWith;\nuse App\\Entities\\Product;\n\n/**\n * @Type(class=Product::class)\n * @SourceField(name="name")\n * @SourceField(name="price", annotations={@Logged, @Right(name="CAN_ACCESS_Price", @FailWith(null)}))\n */\nclass ProductType extends AbstractAnnotatedObjectType\n{\n}\n')),(0,o.yg)("p",null,"Any annotations described in the ",(0,o.yg)("a",{parentName:"p",href:"/docs/6.1/authentication-authorization"},"Authentication and authorization page"),", or any annotation this is actually a ",(0,o.yg)("a",{parentName:"p",href:"/docs/6.1/field-middlewares"},'"field middleware"')," can be used in the ",(0,o.yg)("inlineCode",{parentName:"p"},"@SourceField"),' "annotations" attribute.'),(0,o.yg)("div",{class:"alert alert--warning"},(0,o.yg)("strong",null,"Heads up!"),' The "annotation" attribute in @SourceField and @MagicField is only available as a ',(0,o.yg)("strong",null,"Doctrine annotations"),". You cannot use it in PHP 8 attributes (because PHP 8 attributes cannot be nested)"),(0,o.yg)("h2",{id:"declaring-fields-dynamically-without-annotations"},"Declaring fields dynamically (without annotations)"),(0,o.yg)("p",null,"In some very particular cases, you might not know exactly the list of ",(0,o.yg)("inlineCode",{parentName:"p"},"@SourceField")," annotations at development time.\nIf you need to decide the list of ",(0,o.yg)("inlineCode",{parentName:"p"},"@SourceField")," at runtime, you can implement the ",(0,o.yg)("inlineCode",{parentName:"p"},"FromSourceFieldsInterface"),":"),(0,o.yg)("pre",null,(0,o.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\FromSourceFieldsInterface;\n\n#[Type(class: Product::class)]\nclass ProductType implements FromSourceFieldsInterface\n{\n    /**\n     * Dynamically returns the array of source fields\n     * to be fetched from the original object.\n     *\n     * @return SourceFieldInterface[]\n     */\n    public function getSourceFields(): array\n    {\n        // You may want to enable fields conditionally based on feature flags...\n        if (ENABLE_STATUS_GLOBALLY) {\n            return [\n                new SourceField(['name'=>'status', 'logged'=>true]),\n            ];\n        } else {\n            return [];\n        }\n    }\n}\n")))}p.isMDXComponent=!0}}]);