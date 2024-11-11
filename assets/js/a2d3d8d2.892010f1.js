"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[6123],{19365:(e,t,a)=>{a.d(t,{A:()=>i});var n=a(96540),r=a(20053);const l={tabItem:"tabItem_Ymn6"};function i(e){let{children:t,hidden:a,className:i}=e;return n.createElement("div",{role:"tabpanel",className:(0,r.A)(l.tabItem,i),hidden:a},t)}},11470:(e,t,a)=>{a.d(t,{A:()=>x});var n=a(58168),r=a(96540),l=a(20053),i=a(23104),o=a(56347),u=a(57485),s=a(31682),p=a(89466);function c(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:a,attributes:n,default:r}}=e;return{value:t,label:a,attributes:n,default:r}}))}function d(e){const{values:t,children:a}=e;return(0,r.useMemo)((()=>{const e=t??c(a);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,a])}function y(e){let{value:t,tabValues:a}=e;return a.some((e=>e.value===t))}function m(e){let{queryString:t=!1,groupId:a}=e;const n=(0,o.W6)(),l=function(e){let{queryString:t=!1,groupId:a}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:t,groupId:a});return[(0,u.aZ)(l),(0,r.useCallback)((e=>{if(!l)return;const t=new URLSearchParams(n.location.search);t.set(l,e),n.replace({...n.location,search:t.toString()})}),[l,n])]}function h(e){const{defaultValue:t,queryString:a=!1,groupId:n}=e,l=d(e),[i,o]=(0,r.useState)((()=>function(e){let{defaultValue:t,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!y({value:t,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const n=a.find((e=>e.default))??a[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:t,tabValues:l}))),[u,s]=m({queryString:a,groupId:n}),[c,h]=function(e){let{groupId:t}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(t),[n,l]=(0,p.Dv)(a);return[n,(0,r.useCallback)((e=>{a&&l.set(e)}),[a,l])]}({groupId:n}),f=(()=>{const e=u??c;return y({value:e,tabValues:l})?e:null})();(0,r.useLayoutEffect)((()=>{f&&o(f)}),[f]);return{selectedValue:i,selectValue:(0,r.useCallback)((e=>{if(!y({value:e,tabValues:l}))throw new Error(`Can't select invalid tab value=${e}`);o(e),s(e),h(e)}),[s,h,l]),tabValues:l}}var f=a(92303);const g={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:a,selectedValue:o,selectValue:u,tabValues:s}=e;const p=[],{blockElementScrollPositionUntilNextRender:c}=(0,i.a_)(),d=e=>{const t=e.currentTarget,a=p.indexOf(t),n=s[a].value;n!==o&&(c(t),u(n))},y=e=>{let t=null;switch(e.key){case"Enter":d(e);break;case"ArrowRight":{const a=p.indexOf(e.currentTarget)+1;t=p[a]??p[0];break}case"ArrowLeft":{const a=p.indexOf(e.currentTarget)-1;t=p[a]??p[p.length-1];break}}t?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,l.A)("tabs",{"tabs--block":a},t)},s.map((e=>{let{value:t,label:a,attributes:i}=e;return r.createElement("li",(0,n.A)({role:"tab",tabIndex:o===t?0:-1,"aria-selected":o===t,key:t,ref:e=>p.push(e),onKeyDown:y,onClick:d},i,{className:(0,l.A)("tabs__item",g.tabItem,i?.className,{"tabs__item--active":o===t})}),a??t)})))}function v(e){let{lazy:t,children:a,selectedValue:n}=e;const l=(Array.isArray(a)?a:[a]).filter(Boolean);if(t){const e=l.find((e=>e.props.value===n));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},l.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==n}))))}function T(e){const t=h(e);return r.createElement("div",{className:(0,l.A)("tabs-container",g.tabList)},r.createElement(b,(0,n.A)({},e,t)),r.createElement(v,(0,n.A)({},e,t)))}function x(e){const t=(0,f.A)();return r.createElement(T,(0,n.A)({key:String(t)},e))}},59490:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>p,contentTitle:()=>u,default:()=>m,frontMatter:()=>o,metadata:()=>s,toc:()=>c});var n=a(58168),r=(a(96540),a(15680)),l=(a(67443),a(11470)),i=a(19365);const o={id:"extend-input-type",title:"Extending an input type",sidebar_label:"Extending an input type"},u=void 0,s={unversionedId:"extend-input-type",id:"version-6.0/extend-input-type",title:"Extending an input type",description:"Available in GraphQLite 4.0+",source:"@site/versioned_docs/version-6.0/extend-input-type.mdx",sourceDirName:".",slug:"/extend-input-type",permalink:"/docs/6.0/extend-input-type",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-6.0/extend-input-type.mdx",tags:[],version:"6.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1731361033,formattedLastUpdatedAt:"Nov 11, 2024",frontMatter:{id:"extend-input-type",title:"Extending an input type",sidebar_label:"Extending an input type"},sidebar:"docs",previous:{title:"Custom argument resolving",permalink:"/docs/6.0/argument-resolving"},next:{title:"Class with multiple output types",permalink:"/docs/6.0/multiple-output-types"}},p={},c=[],d={toc:c},y="wrapper";function m(e){let{components:t,...a}=e;return(0,r.yg)(y,(0,n.A)({},d,a,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("small",null,"Available in GraphQLite 4.0+"),(0,r.yg)("div",{class:"alert alert--info"},"If you are not familiar with the ",(0,r.yg)("code",null,"@Factory")," tag, ",(0,r.yg)("a",{href:"input-types"},'read first the "input types" guide'),"."),(0,r.yg)("p",null,"Fields exposed in a GraphQL input type do not need to be all part of the factory method."),(0,r.yg)("p",null,"Just like with output type (that can be ",(0,r.yg)("a",{parentName:"p",href:"/docs/6.0/extend-type"},"extended using the ",(0,r.yg)("inlineCode",{parentName:"a"},"ExtendType")," annotation"),"), you can extend/modify\nan input type using the ",(0,r.yg)("inlineCode",{parentName:"p"},"@Decorate")," annotation."),(0,r.yg)("p",null,"Use the ",(0,r.yg)("inlineCode",{parentName:"p"},"@Decorate")," annotation to add additional fields to an input type that is already declared by a ",(0,r.yg)("inlineCode",{parentName:"p"},"@Factory")," annotation,\nor to modify the returned object."),(0,r.yg)("div",{class:"alert alert--info"},"The ",(0,r.yg)("code",null,"@Decorate")," annotation is very useful in scenarios where you cannot touch the ",(0,r.yg)("code",null,"@Factory")," method. This can happen if the ",(0,r.yg)("code",null,"@Factory")," method is defined in a third-party library or if the ",(0,r.yg)("code",null,"@Factory")," method is part of auto-generated code."),(0,r.yg)("p",null,"Let's assume you have a ",(0,r.yg)("inlineCode",{parentName:"p"},"Filter")," class used as an input type. You most certainly have a ",(0,r.yg)("inlineCode",{parentName:"p"},"@Factory")," to create the input type."),(0,r.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(i.A,{value:"php8",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class MyFactory\n{\n    #[Factory]\n    public function createFilter(string $name): Filter\n    {\n        // Let's assume you have a flexible 'Filter' class that can accept any kind of filter\n        $filter = new Filter();\n        $filter->addFilter('name', $name);\n        return $filter;\n    }\n}\n"))),(0,r.yg)(i.A,{value:"php7",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class MyFactory\n{\n    /**\n     * @Factory()\n     */\n    public function createFilter(string $name): Filter\n    {\n        // Let's assume you have a flexible 'Filter' class that can accept any kind of filter\n        $filter = new Filter();\n        $filter->addFilter('name', $name);\n        return $filter;\n    }\n}\n")))),(0,r.yg)("p",null,"Assuming you ",(0,r.yg)("strong",{parentName:"p"},"cannot"),' modify the code of this factory, you can still modify the GraphQL input type generated by\nadding a "decorator" around the factory.'),(0,r.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(i.A,{value:"php8",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class MyDecorator\n{\n    #[Decorate(inputTypeName: \"FilterInput\")]\n    public function addTypeFilter(Filter $filter, string $type): Filter\n    {\n        $filter->addFilter('type', $type);\n        return $filter;\n    }\n}\n"))),(0,r.yg)(i.A,{value:"php7",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"class MyDecorator\n{\n    /**\n     * @Decorate(inputTypeName=\"FilterInput\")\n     */\n    public function addTypeFilter(Filter $filter, string $type): Filter\n    {\n        $filter->addFilter('type', $type);\n        return $filter;\n    }\n}\n")))),(0,r.yg)("p",null,'In the example above, the "Filter" input type is modified. We add an additional "type" field to the input type.'),(0,r.yg)("p",null,"A few things to notice:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"The decorator takes the object generated by the factory as first argument"),(0,r.yg)("li",{parentName:"ul"},"The decorator MUST return an object of the same type (or a sub-type)"),(0,r.yg)("li",{parentName:"ul"},"The decorator CAN contain additional parameters. They will be added to the fields of the GraphQL input type."),(0,r.yg)("li",{parentName:"ul"},"The ",(0,r.yg)("inlineCode",{parentName:"li"},"@Decorate")," annotation must contain a ",(0,r.yg)("inlineCode",{parentName:"li"},"inputTypeName")," attribute that contains the name of the GraphQL input type\nthat is decorated. If you did not specify this name in the ",(0,r.yg)("inlineCode",{parentName:"li"},"@Factory"),' annotation, this is by default the name of the\nPHP class + "Input" (for instance: "Filter" => "FilterInput")')),(0,r.yg)("div",{class:"alert alert--warning"},(0,r.yg)("strong",null,"Heads up!")," The ",(0,r.yg)("code",null,"MyDecorator")," class must exist in the container of your application and the container identifier MUST be the fully qualified class name.",(0,r.yg)("br",null),(0,r.yg)("br",null),"If you are using the Symfony bundle (or a framework with autowiring like Laravel), this is usually not an issue as the container will automatically create the controller entry if you do not explicitly declare it."))}m.isMDXComponent=!0}}]);