"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1126],{19365:(e,t,a)=>{a.d(t,{A:()=>r});var n=a(96540),l=a(20053);const u={tabItem:"tabItem_Ymn6"};function r(e){let{children:t,hidden:a,className:r}=e;return n.createElement("div",{role:"tabpanel",className:(0,l.A)(u.tabItem,r),hidden:a},t)}},11470:(e,t,a)=>{a.d(t,{A:()=>P});var n=a(58168),l=a(96540),u=a(20053),r=a(23104),i=a(56347),p=a(57485),s=a(31682),o=a(89466);function d(e){return function(e){return l.Children.map(e,(e=>{if(!e||(0,l.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:a,attributes:n,default:l}}=e;return{value:t,label:a,attributes:n,default:l}}))}function c(e){const{values:t,children:a}=e;return(0,l.useMemo)((()=>{const e=t??d(a);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,a])}function y(e){let{value:t,tabValues:a}=e;return a.some((e=>e.value===t))}function m(e){let{queryString:t=!1,groupId:a}=e;const n=(0,i.W6)(),u=function(e){let{queryString:t=!1,groupId:a}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:t,groupId:a});return[(0,p.aZ)(u),(0,l.useCallback)((e=>{if(!u)return;const t=new URLSearchParams(n.location.search);t.set(u,e),n.replace({...n.location,search:t.toString()})}),[u,n])]}function h(e){const{defaultValue:t,queryString:a=!1,groupId:n}=e,u=c(e),[r,i]=(0,l.useState)((()=>function(e){let{defaultValue:t,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!y({value:t,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const n=a.find((e=>e.default))??a[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:t,tabValues:u}))),[p,s]=m({queryString:a,groupId:n}),[d,h]=function(e){let{groupId:t}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(t),[n,u]=(0,o.Dv)(a);return[n,(0,l.useCallback)((e=>{a&&u.set(e)}),[a,u])]}({groupId:n}),g=(()=>{const e=p??d;return y({value:e,tabValues:u})?e:null})();(0,l.useLayoutEffect)((()=>{g&&i(g)}),[g]);return{selectedValue:r,selectValue:(0,l.useCallback)((e=>{if(!y({value:e,tabValues:u}))throw new Error(`Can't select invalid tab value=${e}`);i(e),s(e),h(e)}),[s,h,u]),tabValues:u}}var g=a(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:a,selectedValue:i,selectValue:p,tabValues:s}=e;const o=[],{blockElementScrollPositionUntilNextRender:d}=(0,r.a_)(),c=e=>{const t=e.currentTarget,a=o.indexOf(t),n=s[a].value;n!==i&&(d(t),p(n))},y=e=>{let t=null;switch(e.key){case"Enter":c(e);break;case"ArrowRight":{const a=o.indexOf(e.currentTarget)+1;t=o[a]??o[0];break}case"ArrowLeft":{const a=o.indexOf(e.currentTarget)-1;t=o[a]??o[o.length-1];break}}t?.focus()};return l.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,u.A)("tabs",{"tabs--block":a},t)},s.map((e=>{let{value:t,label:a,attributes:r}=e;return l.createElement("li",(0,n.A)({role:"tab",tabIndex:i===t?0:-1,"aria-selected":i===t,key:t,ref:e=>o.push(e),onKeyDown:y,onClick:c},r,{className:(0,u.A)("tabs__item",f.tabItem,r?.className,{"tabs__item--active":i===t})}),a??t)})))}function v(e){let{lazy:t,children:a,selectedValue:n}=e;const u=(Array.isArray(a)?a:[a]).filter(Boolean);if(t){const e=u.find((e=>e.props.value===n));return e?(0,l.cloneElement)(e,{className:"margin-top--md"}):null}return l.createElement("div",{className:"margin-top--md"},u.map(((e,t)=>(0,l.cloneElement)(e,{key:t,hidden:e.props.value!==n}))))}function T(e){const t=h(e);return l.createElement("div",{className:(0,u.A)("tabs-container",f.tabList)},l.createElement(b,(0,n.A)({},e,t)),l.createElement(v,(0,n.A)({},e,t)))}function P(e){const t=(0,g.A)();return l.createElement(T,(0,n.A)({key:String(t)},e))}},10149:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>o,contentTitle:()=>p,default:()=>m,frontMatter:()=>i,metadata:()=>s,toc:()=>d});var n=a(58168),l=(a(96540),a(15680)),u=(a(67443),a(11470)),r=a(19365);const i={id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},p=void 0,s={unversionedId:"multiple-output-types",id:"version-4.3/multiple-output-types",title:"Mapping multiple output types for the same class",description:"Available in GraphQLite 4.0+",source:"@site/versioned_docs/version-4.3/multiple-output-types.mdx",sourceDirName:".",slug:"/multiple-output-types",permalink:"/docs/4.3/multiple-output-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.3/multiple-output-types.mdx",tags:[],version:"4.3",lastUpdatedBy:"sudevva",lastUpdatedAt:1728787444,formattedLastUpdatedAt:"Oct 13, 2024",frontMatter:{id:"multiple-output-types",title:"Mapping multiple output types for the same class",sidebar_label:"Class with multiple output types"},sidebar:"version-4.3/docs",previous:{title:"Extending an input type",permalink:"/docs/4.3/extend-input-type"},next:{title:"Symfony specific features",permalink:"/docs/4.3/symfony-bundle-advanced"}},o={},d=[{value:"Example",id:"example",level:2},{value:"Extending a non-default type",id:"extending-a-non-default-type",level:2}],c={toc:d},y="wrapper";function m(e){let{components:t,...a}=e;return(0,l.yg)(y,(0,n.A)({},c,a,{components:t,mdxType:"MDXLayout"}),(0,l.yg)("small",null,"Available in GraphQLite 4.0+"),(0,l.yg)("p",null,"In most cases, you have one PHP class and you want to map it to one GraphQL output type."),(0,l.yg)("p",null,"But in very specific cases, you may want to use different GraphQL output type for the same class.\nFor instance, depending on the context, you might want to prevent the user from accessing some fields of your object."),(0,l.yg)("p",null,'To do so, you need to create 2 output types for the same PHP class. You typically do this using the "default" attribute of the ',(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.yg)("h2",{id:"example"},"Example"),(0,l.yg)("p",null,"Here is an example. Say we are manipulating products. When I query a ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," details, I want to have access to all fields.\nBut for some reason, I don't want to expose the price field of a product if I query the list of all products."),(0,l.yg)(u.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,l.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"Product"),' class is declaring a classic GraphQL output type named "Product".'),(0,l.yg)(u.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'#[Type(class: Product::class, name: "LimitedProduct", default: false)]\n#[SourceField(name: "name")]\nclass LimitedProductType\n{\n    // ...\n}\n'))),(0,l.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(class=Product::class, name="LimitedProduct", default=false)\n * @SourceField(name="name")\n */\nclass LimitedProductType\n{\n    // ...\n}\n')))),(0,l.yg)("p",null,"The ",(0,l.yg)("inlineCode",{parentName:"p"},"LimitedProductType")," also declares an ",(0,l.yg)("a",{parentName:"p",href:"/docs/4.3/external-type-declaration"},'"external" type')," mapping the ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," class.\nBut pay special attention to the ",(0,l.yg)("inlineCode",{parentName:"p"},"@Type")," annotation."),(0,l.yg)("p",null,"First of all, we specify ",(0,l.yg)("inlineCode",{parentName:"p"},'name="LimitedProduct"'),'. This is useful to avoid having colliding names with the "Product" GraphQL output type\nthat is already declared.'),(0,l.yg)("p",null,"Then, we specify ",(0,l.yg)("inlineCode",{parentName:"p"},"default=false"),". This means that by default, the ",(0,l.yg)("inlineCode",{parentName:"p"},"Product")," class should not be mapped to the ",(0,l.yg)("inlineCode",{parentName:"p"},"LimitedProductType"),".\nThis type will only be used when we explicitly request it."),(0,l.yg)("p",null,"Finally, we can write our requests:"),(0,l.yg)(u.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     */\n    #[Field]\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @return Product[]\n     */\n    #[Field(outputType: "[LimitedProduct!]!")]\n    public function getProducts(): array { /* ... */ }\n}\n'))),(0,l.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'class ProductController\n{\n    /**\n     * This field will use the default type.\n     *\n     * @Field\n     */\n    public function getProduct(int $id): Product { /* ... */ }\n\n    /**\n     * Because we use the "outputType" attribute, this field will use the other type.\n     *\n     * @Field(outputType="[LimitedProduct!]!")\n     * @return Product[]\n     */\n    public function getProducts(): array { /* ... */ }\n}\n')))),(0,l.yg)("p",null,'Notice how the "outputType" attribute is used in the ',(0,l.yg)("inlineCode",{parentName:"p"},"@Field")," annotation to force the output type."),(0,l.yg)("p",null,"Is a result, when the end user calls the ",(0,l.yg)("inlineCode",{parentName:"p"},"product")," query, we will have the possibility to fetch the ",(0,l.yg)("inlineCode",{parentName:"p"},"name")," and ",(0,l.yg)("inlineCode",{parentName:"p"},"price")," fields,\nbut if he calls the ",(0,l.yg)("inlineCode",{parentName:"p"},"products")," query, each product in the list will have a ",(0,l.yg)("inlineCode",{parentName:"p"},"name")," field but no ",(0,l.yg)("inlineCode",{parentName:"p"},"price")," field. We managed\nto successfully expose a different set of fields based on the query context."),(0,l.yg)("h2",{id:"extending-a-non-default-type"},"Extending a non-default type"),(0,l.yg)("p",null,"If you want to extend a type using the ",(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation and if this type is declared as non-default,\nyou need to target the type by name instead of by class."),(0,l.yg)("p",null,"So instead of writing:"),(0,l.yg)(u.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"#[ExtendType(class: Product::class)]\n"))),(0,l.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @ExtendType(class=Product::class)\n */\n")))),(0,l.yg)("p",null,"you will write:"),(0,l.yg)(u.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'#[ExtendType(name: "LimitedProduct")]\n'))),(0,l.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,l.yg)("pre",null,(0,l.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @ExtendType(name="LimitedProduct")\n */\n')))),(0,l.yg)("p",null,'Notice how we use the "name" attribute instead of the "class" attribute in the ',(0,l.yg)("inlineCode",{parentName:"p"},"@ExtendType")," annotation."))}m.isMDXComponent=!0}}]);