"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2021],{5162:(e,t,a)=>{a.d(t,{Z:()=>r});var n=a(7294),l=a(6010);const u={tabItem:"tabItem_Ymn6"};function r(e){let{children:t,hidden:a,className:r}=e;return n.createElement("div",{role:"tabpanel",className:(0,l.Z)(u.tabItem,r),hidden:a},t)}},4866:(e,t,a)=>{a.d(t,{Z:()=>T});var n=a(7462),l=a(7294),u=a(6010),r=a(2466),i=a(6550),p=a(1980),o=a(7392),s=a(12);function d(e){return function(e){return l.Children.map(e,(e=>{if(!e||(0,l.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:a,attributes:n,default:l}}=e;return{value:t,label:a,attributes:n,default:l}}))}function c(e){const{values:t,children:a}=e;return(0,l.useMemo)((()=>{const e=t??d(a);return function(e){const t=(0,o.l)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,a])}function m(e){let{value:t,tabValues:a}=e;return a.some((e=>e.value===t))}function h(e){let{queryString:t=!1,groupId:a}=e;const n=(0,i.k6)(),u=function(e){let{queryString:t=!1,groupId:a}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:t,groupId:a});return[(0,p._X)(u),(0,l.useCallback)((e=>{if(!u)return;const t=new URLSearchParams(n.location.search);t.set(u,e),n.replace({...n.location,search:t.toString()})}),[u,n])]}function f(e){const{defaultValue:t,queryString:a=!1,groupId:n}=e,u=c(e),[r,i]=(0,l.useState)((()=>function(e){let{defaultValue:t,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!m({value:t,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const n=a.find((e=>e.default))??a[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:t,tabValues:u}))),[p,o]=h({queryString:a,groupId:n}),[d,f]=function(e){let{groupId:t}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(t),[n,u]=(0,s.Nk)(a);return[n,(0,l.useCallback)((e=>{a&&u.set(e)}),[a,u])]}({groupId:n}),y=(()=>{const e=p??d;return m({value:e,tabValues:u})?e:null})();(0,l.useLayoutEffect)((()=>{y&&i(y)}),[y]);return{selectedValue:r,selectValue:(0,l.useCallback)((e=>{if(!m({value:e,tabValues:u}))throw new Error(`Can't select invalid tab value=${e}`);i(e),o(e),f(e)}),[o,f,u]),tabValues:u}}var y=a(2389);const b={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function k(e){let{className:t,block:a,selectedValue:i,selectValue:p,tabValues:o}=e;const s=[],{blockElementScrollPositionUntilNextRender:d}=(0,r.o5)(),c=e=>{const t=e.currentTarget,a=s.indexOf(t),n=o[a].value;n!==i&&(d(t),p(n))},m=e=>{let t=null;switch(e.key){case"Enter":c(e);break;case"ArrowRight":{const a=s.indexOf(e.currentTarget)+1;t=s[a]??s[0];break}case"ArrowLeft":{const a=s.indexOf(e.currentTarget)-1;t=s[a]??s[s.length-1];break}}t?.focus()};return l.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,u.Z)("tabs",{"tabs--block":a},t)},o.map((e=>{let{value:t,label:a,attributes:r}=e;return l.createElement("li",(0,n.Z)({role:"tab",tabIndex:i===t?0:-1,"aria-selected":i===t,key:t,ref:e=>s.push(e),onKeyDown:m,onClick:c},r,{className:(0,u.Z)("tabs__item",b.tabItem,r?.className,{"tabs__item--active":i===t})}),a??t)})))}function v(e){let{lazy:t,children:a,selectedValue:n}=e;const u=(Array.isArray(a)?a:[a]).filter(Boolean);if(t){const e=u.find((e=>e.props.value===n));return e?(0,l.cloneElement)(e,{className:"margin-top--md"}):null}return l.createElement("div",{className:"margin-top--md"},u.map(((e,t)=>(0,l.cloneElement)(e,{key:t,hidden:e.props.value!==n}))))}function g(e){const t=f(e);return l.createElement("div",{className:(0,u.Z)("tabs-container",b.tabList)},l.createElement(k,(0,n.Z)({},e,t)),l.createElement(v,(0,n.Z)({},e,t)))}function T(e){const t=(0,y.Z)();return l.createElement(g,(0,n.Z)({key:String(t)},e))}},3746:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>s,contentTitle:()=>p,default:()=>h,frontMatter:()=>i,metadata:()=>o,toc:()=>d});var n=a(7462),l=(a(7294),a(3905)),u=(a(1839),a(4866)),r=a(5162);const i={id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},p=void 0,o={unversionedId:"multiple-output-types",id:"version-5.0/multiple-output-types",title:"Mapping multiple output types for the same class",description:"Available in GraphQLite 4.0+",source:"@site/versioned_docs/version-5.0/multiple-output-types.mdx",sourceDirName:".",slug:"/multiple-output-types",permalink:"/docs/5.0/multiple-output-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-5.0/multiple-output-types.mdx",tags:[],version:"5.0",lastUpdatedBy:"Oleksandr Prypkhan",lastUpdatedAt:1701656769,formattedLastUpdatedAt:"Dec 4, 2023",frontMatter:{id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},sidebar:"version-5.0/docs",previous:{title:"Extending an input type",permalink:"/docs/5.0/extend-input-type"},next:{title:"Symfony specific features",permalink:"/docs/5.0/symfony-bundle-advanced"}},s={},d=[{value:"Example",id:"example",level:2},{value:"Extending a non-default type",id:"extending-a-non-default-type",level:2}],c={toc:d},m="wrapper";function h(e){let{components:t,...a}=e;return(0,l.kt)(m,(0,n.Z)({},c,a,{components:t,mdxType:"MDXLayout"}),(0,l.kt)("small",null,"Available in GraphQLite 4.0+"),(0,l.kt)("p",null,"In most cases, you have one PHP class and you want to map it to one GraphQL output type."),(0,l.kt)("p",null,"But in very specific cases, you may want to use different GraphQL output type for the same class.\nFor instance, depending on the context, you might want to prevent the user from accessing some fields of your object."),(0,l.kt)("p",null,'To do so, you need to create 2 output types for the same PHP class. You typically do this using the "default" attribute of the ',(0,l.kt)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.kt)("h2",{id:"example"},"Example"),(0,l.kt)("p",null,"Here is an example. Say we are manipulating products. When I query a ",(0,l.kt)("inlineCode",{parentName:"p"},"Product")," details, I want to have access to all fields.\nBut for some reason, I don't want to expose the price field of a product if I query the list of all products."),(0,l.kt)(u.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(r.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,l.kt)(r.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,l.kt)("p",null,"The ",(0,l.kt)("inlineCode",{parentName:"p"},"Product"),' class is declaring a classic GraphQL output type named "Product".'),(0,l.kt)(u.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(r.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'#[Type(class: Product::class, name: "LimitedProduct", default: false)]\n#[SourceField(name: "name")]\nclass LimitedProductType\n{\n    // ...\n}\n'))),(0,l.kt)(r.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(class=Product::class, name="LimitedProduct", default=false)\n * @SourceField(name="name")\n */\nclass LimitedProductType\n{\n    // ...\n}\n')))),(0,l.kt)("p",null,"The ",(0,l.kt)("inlineCode",{parentName:"p"},"LimitedProductType")," also declares an ",(0,l.kt)("a",{parentName:"p",href:"/docs/5.0/external-type-declaration"},'"external" type')," mapping the ",(0,l.kt)("inlineCode",{parentName:"p"},"Product")," class.\nBut pay special attention to the ",(0,l.kt)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.kt)("p",null,"First of all, we specify ",(0,l.kt)("inlineCode",{parentName:"p"},'name="LimitedProduct"'),'. This is useful to avoid having colliding names with the "Product" GraphQL output type\nthat is already declared.'),(0,l.kt)("p",null,"Then, we specify ",(0,l.kt)("inlineCode",{parentName:"p"},"default=false"),". This means that by default, the ",(0,l.kt)("inlineCode",{parentName:"p"},"Product")," class should not be mapped to the ",(0,l.kt)("inlineCode",{parentName:"p"},"LimitedProductType"),".\nThis type will only be used when we explicitly request it."),(0,l.kt)("p",null,"Finally, we can write our requests:"),(0,l.kt)(u.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(r.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     */\n    #[Field]\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @return Product[]\n     */\n    #[Field(outputType: "[LimitedProduct!]!")]\n    public function getProducts(): array { /* ... */ }\n}\n'))),(0,l.kt)(r.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     *\n     * @Field\n     */\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @Field(outputType="[LimitedProduct!]!")\n     * @return Product[]\n     */\n    public function getProducts(): array { /* ... */ }\n}\n')))),(0,l.kt)("p",null,'Notice how the "outputType" attribute is used in the ',(0,l.kt)("inlineCode",{parentName:"p"},"@Field")," annotation to force the output type."),(0,l.kt)("p",null,"Is a result, when the end user calls the ",(0,l.kt)("inlineCode",{parentName:"p"},"product")," query, we will have the possibility to fetch the ",(0,l.kt)("inlineCode",{parentName:"p"},"name")," and ",(0,l.kt)("inlineCode",{parentName:"p"},"price")," fields,\nbut if he calls the ",(0,l.kt)("inlineCode",{parentName:"p"},"products")," query, each product in the list will have a ",(0,l.kt)("inlineCode",{parentName:"p"},"name")," field but no ",(0,l.kt)("inlineCode",{parentName:"p"},"price")," field. We managed\nto successfully expose a different set of fields based on the query context."),(0,l.kt)("h2",{id:"extending-a-non-default-type"},"Extending a non-default type"),(0,l.kt)("p",null,"If you want to extend a type using the ",(0,l.kt)("inlineCode",{parentName:"p"},"@ExtendType")," annotation and if this type is declared as non-default,\nyou need to target the type by name instead of by class."),(0,l.kt)("p",null,"So instead of writing:"),(0,l.kt)(u.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(r.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"#[ExtendType(class: Product::class)]\n"))),(0,l.kt)(r.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @ExtendType(class=Product::class)\n */\n")))),(0,l.kt)("p",null,"you will write:"),(0,l.kt)(u.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(r.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'#[ExtendType(name: "LimitedProduct")]\n'))),(0,l.kt)(r.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},'/**\n * @ExtendType(name="LimitedProduct")\n */\n')))),(0,l.kt)("p",null,'Notice how we use the "name" attribute instead of the "class" attribute in the ',(0,l.kt)("inlineCode",{parentName:"p"},"@ExtendType")," annotation."))}h.isMDXComponent=!0}}]);