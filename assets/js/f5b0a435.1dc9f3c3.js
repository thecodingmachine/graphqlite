"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[7373],{19365:(e,n,a)=>{a.d(n,{A:()=>r});var t=a(96540),p=a(20053);const l={tabItem:"tabItem_Ymn6"};function r(e){let{children:n,hidden:a,className:r}=e;return t.createElement("div",{role:"tabpanel",className:(0,p.A)(l.tabItem,r),hidden:a},n)}},11470:(e,n,a)=>{a.d(n,{A:()=>N});var t=a(58168),p=a(96540),l=a(20053),r=a(23104),i=a(56347),s=a(57485),u=a(31682),o=a(89466);function c(e){return function(e){return p.Children.map(e,(e=>{if(!e||(0,p.isValidElement)(e)&&function(e){const{props:n}=e;return!!n&&"object"==typeof n&&"value"in n}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:n,label:a,attributes:t,default:p}}=e;return{value:n,label:a,attributes:t,default:p}}))}function y(e){const{values:n,children:a}=e;return(0,p.useMemo)((()=>{const e=n??c(a);return function(e){const n=(0,u.X)(e,((e,n)=>e.value===n.value));if(n.length>0)throw new Error(`Docusaurus error: Duplicate values "${n.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[n,a])}function m(e){let{value:n,tabValues:a}=e;return a.some((e=>e.value===n))}function d(e){let{queryString:n=!1,groupId:a}=e;const t=(0,i.W6)(),l=function(e){let{queryString:n=!1,groupId:a}=e;if("string"==typeof n)return n;if(!1===n)return null;if(!0===n&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:n,groupId:a});return[(0,s.aZ)(l),(0,p.useCallback)((e=>{if(!l)return;const n=new URLSearchParams(t.location.search);n.set(l,e),t.replace({...t.location,search:n.toString()})}),[l,t])]}function g(e){const{defaultValue:n,queryString:a=!1,groupId:t}=e,l=y(e),[r,i]=(0,p.useState)((()=>function(e){let{defaultValue:n,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(n){if(!m({value:n,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${n}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return n}const t=a.find((e=>e.default))??a[0];if(!t)throw new Error("Unexpected error: 0 tabValues");return t.value}({defaultValue:n,tabValues:l}))),[s,u]=d({queryString:a,groupId:t}),[c,g]=function(e){let{groupId:n}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(n),[t,l]=(0,o.Dv)(a);return[t,(0,p.useCallback)((e=>{a&&l.set(e)}),[a,l])]}({groupId:t}),h=(()=>{const e=s??c;return m({value:e,tabValues:l})?e:null})();(0,p.useLayoutEffect)((()=>{h&&i(h)}),[h]);return{selectedValue:r,selectValue:(0,p.useCallback)((e=>{if(!m({value:e,tabValues:l}))throw new Error(`Can't select invalid tab value=${e}`);i(e),u(e),g(e)}),[u,g,l]),tabValues:l}}var h=a(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:n,block:a,selectedValue:i,selectValue:s,tabValues:u}=e;const o=[],{blockElementScrollPositionUntilNextRender:c}=(0,r.a_)(),y=e=>{const n=e.currentTarget,a=o.indexOf(n),t=u[a].value;t!==i&&(c(n),s(t))},m=e=>{let n=null;switch(e.key){case"Enter":y(e);break;case"ArrowRight":{const a=o.indexOf(e.currentTarget)+1;n=o[a]??o[0];break}case"ArrowLeft":{const a=o.indexOf(e.currentTarget)-1;n=o[a]??o[o.length-1];break}}n?.focus()};return p.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,l.A)("tabs",{"tabs--block":a},n)},u.map((e=>{let{value:n,label:a,attributes:r}=e;return p.createElement("li",(0,t.A)({role:"tab",tabIndex:i===n?0:-1,"aria-selected":i===n,key:n,ref:e=>o.push(e),onKeyDown:m,onClick:y},r,{className:(0,l.A)("tabs__item",f.tabItem,r?.className,{"tabs__item--active":i===n})}),a??n)})))}function v(e){let{lazy:n,children:a,selectedValue:t}=e;const l=(Array.isArray(a)?a:[a]).filter(Boolean);if(n){const e=l.find((e=>e.props.value===t));return e?(0,p.cloneElement)(e,{className:"margin-top--md"}):null}return p.createElement("div",{className:"margin-top--md"},l.map(((e,n)=>(0,p.cloneElement)(e,{key:n,hidden:e.props.value!==t}))))}function T(e){const n=g(e);return p.createElement("div",{className:(0,l.A)("tabs-container",f.tabList)},p.createElement(b,(0,t.A)({},e,n)),p.createElement(v,(0,t.A)({},e,n)))}function N(e){const n=(0,h.A)();return p.createElement(T,(0,t.A)({key:String(n)},e))}},8710:(e,n,a)=>{a.r(n),a.d(n,{assets:()=>o,contentTitle:()=>s,default:()=>d,frontMatter:()=>i,metadata:()=>u,toc:()=>c});var t=a(58168),p=(a(96540),a(15680)),l=(a(67443),a(11470)),r=a(19365);const i={id:"type_mapping",title:"Type mapping",sidebar_label:"Type mapping",original_id:"type_mapping"},s=void 0,u={unversionedId:"type_mapping",id:"version-4.1/type_mapping",title:"Type mapping",description:"As explained in the queries section, the job of GraphQLite is to create GraphQL types from PHP types.",source:"@site/versioned_docs/version-4.1/type_mapping.mdx",sourceDirName:".",slug:"/type_mapping",permalink:"/docs/4.1/type_mapping",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.1/type_mapping.mdx",tags:[],version:"4.1",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1731361033,formattedLastUpdatedAt:"Nov 11, 2024",frontMatter:{id:"type_mapping",title:"Type mapping",sidebar_label:"Type mapping",original_id:"type_mapping"},sidebar:"version-4.1/docs",previous:{title:"Mutations",permalink:"/docs/4.1/mutations"},next:{title:"Autowiring services",permalink:"/docs/4.1/autowiring"}},o={},c=[{value:"Scalar mapping",id:"scalar-mapping",level:2},{value:"Class mapping",id:"class-mapping",level:2},{value:"Array mapping",id:"array-mapping",level:2},{value:"ID mapping",id:"id-mapping",level:2},{value:"Force the outputType",id:"force-the-outputtype",level:3},{value:"ID class",id:"id-class",level:3},{value:"Date mapping",id:"date-mapping",level:2},{value:"Union types",id:"union-types",level:2},{value:"Enum types",id:"enum-types",level:2},{value:"Deprecation of fields",id:"deprecation-of-fields",level:2},{value:"More scalar types",id:"more-scalar-types",level:2}],y={toc:c},m="wrapper";function d(e){let{components:n,...a}=e;return(0,p.yg)(m,(0,t.A)({},y,a,{components:n,mdxType:"MDXLayout"}),(0,p.yg)("p",null,"As explained in the ",(0,p.yg)("a",{parentName:"p",href:"/docs/4.1/queries"},"queries")," section, the job of GraphQLite is to create GraphQL types from PHP types."),(0,p.yg)("h2",{id:"scalar-mapping"},"Scalar mapping"),(0,p.yg)("p",null,"Scalar PHP types can be type-hinted to the corresponding GraphQL types:"),(0,p.yg)("ul",null,(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"string")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"int")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"bool")),(0,p.yg)("li",{parentName:"ul"},(0,p.yg)("inlineCode",{parentName:"li"},"float"))),(0,p.yg)("p",null,"For instance:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    #[Query]\n    public function hello(string $name): string\n    {\n        return 'Hello ' . $name;\n    }\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Controller;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Query;\n\nclass MyController\n{\n    /**\n     * @Query\n     */\n    public function hello(string $name): string\n    {\n        return 'Hello ' . $name;\n    }\n}\n")))),(0,p.yg)("h2",{id:"class-mapping"},"Class mapping"),(0,p.yg)("p",null,"When returning a PHP class in a query, you must annotate this class using ",(0,p.yg)("inlineCode",{parentName:"p"},"@Type")," and ",(0,p.yg)("inlineCode",{parentName:"p"},"@Field")," annotations:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n#[Type]\nclass Product\n{\n    // ...\n\n    #[Field]\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    #[Field]\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     */\n    public function getPrice(): ?float\n    {\n        return $this->price;\n    }\n}\n")))),(0,p.yg)("p",null,(0,p.yg)("strong",{parentName:"p"},"Note:")," The GraphQL output type name generated by GraphQLite is equal to the class name of the PHP class. So if your\nPHP class is ",(0,p.yg)("inlineCode",{parentName:"p"},"App\\Entities\\Product"),', then the GraphQL type will be named "Product".'),(0,p.yg)("p",null,'In case you have several types with the same class name in different namespaces, you will face a naming collision.\nHopefully, you can force the name of the GraphQL output type using the "name" attribute:'),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'#[Type(name: "MyProduct")]\nclass Product { /* ... */ }\n'))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Type(name="MyProduct")\n */\nclass Product { /* ... */ }\n')))),(0,p.yg)("div",{class:"alert alert--info"},"You can also put a ",(0,p.yg)("a",{href:"inheritance-interfaces#mapping-interfaces"},(0,p.yg)("code",null,"@Type")," annotation on a PHP interface to map your code to a GraphQL interface"),"."),(0,p.yg)("h2",{id:"array-mapping"},"Array mapping"),(0,p.yg)("p",null,"You can type-hint against arrays (or iterators) as long as you add a detailed ",(0,p.yg)("inlineCode",{parentName:"p"},"@return")," statement in the PHPDoc."),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @return User[] <=== we specify that the array is an array of User objects.\n */\n#[Query]\npublic function users(int $limit, int $offset): array\n{\n    // Some code that returns an array of "users".\n}\n'))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Query\n * @return User[] <=== we specify that the array is an array of User objects.\n */\npublic function users(int $limit, int $offset): array\n{\n    // Some code that returns an array of "users".\n}\n')))),(0,p.yg)("h2",{id:"id-mapping"},"ID mapping"),(0,p.yg)("p",null,"GraphQL comes with a native ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," type. PHP has no such type."),(0,p.yg)("p",null,"There are two ways with GraphQLite to handle such type."),(0,p.yg)("h3",{id:"force-the-outputtype"},"Force the outputType"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'#[Field(outputType: "ID")]\npublic function getId(): string\n{\n    // ...\n}\n'))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Field(outputType="ID")\n */\npublic function getId(): string\n{\n    // ...\n}\n')))),(0,p.yg)("p",null,"Using the ",(0,p.yg)("inlineCode",{parentName:"p"},"outputType")," attribute of the ",(0,p.yg)("inlineCode",{parentName:"p"},"@Field")," annotation, you can force the output type to ",(0,p.yg)("inlineCode",{parentName:"p"},"ID"),"."),(0,p.yg)("p",null,"You can learn more about forcing output types in the ",(0,p.yg)("a",{parentName:"p",href:"/docs/4.1/custom-types"},"custom types section"),"."),(0,p.yg)("h3",{id:"id-class"},"ID class"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n#[Field]\npublic function getId(): ID\n{\n    // ...\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n/**\n * @Field\n */\npublic function getId(): ID\n{\n    // ...\n}\n")))),(0,p.yg)("p",null,"Note that you can also use the ",(0,p.yg)("inlineCode",{parentName:"p"},"ID")," class as an input type:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n#[Mutation]\npublic function save(ID $id, string $name): Product\n{\n    // ...\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Types\\ID;\n\n/**\n * @Mutation\n */\npublic function save(ID $id, string $name): Product\n{\n    // ...\n}\n")))),(0,p.yg)("h2",{id:"date-mapping"},"Date mapping"),(0,p.yg)("p",null,"Out of the box, GraphQL does not have a ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime")," type, but we took the liberty to add one, with sensible defaults."),(0,p.yg)("p",null,"When used as an output type, ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTimeImmutable")," or ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTimeInterface")," PHP classes are\nautomatically mapped to this ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime")," GraphQL type."),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"#[Field]\npublic function getDate(): \\DateTimeInterface\n{\n    return $this->date;\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Field\n */\npublic function getDate(): \\DateTimeInterface\n{\n    return $this->date;\n}\n")))),(0,p.yg)("p",null,"The ",(0,p.yg)("inlineCode",{parentName:"p"},"date")," field will be of type ",(0,p.yg)("inlineCode",{parentName:"p"},"DateTime"),". In the returned JSON response to a query, the date is formatted as a string\nin the ",(0,p.yg)("strong",{parentName:"p"},"ISO8601")," format (aka ATOM format)."),(0,p.yg)("div",{class:"alert alert--danger"},"PHP ",(0,p.yg)("code",null,"DateTime")," type is not supported."),(0,p.yg)("h2",{id:"union-types"},"Union types"),(0,p.yg)("p",null,"You can create a GraphQL union type ",(0,p.yg)("em",{parentName:"p"},"on the fly")," using the pipe ",(0,p.yg)("inlineCode",{parentName:"p"},"|")," operator in the PHPDoc:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @return Company|Contact <== can return a company OR a contact.\n */\n#[Query]\npublic function companyOrContact(int $id)\n{\n    // Some code that returns a company or a contact.\n}\n"))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"/**\n * @Query\n * @return Company|Contact <== can return a company OR a contact.\n */\npublic function companyOrContact(int $id)\n{\n    // Some code that returns a company or a contact.\n}\n")))),(0,p.yg)("h2",{id:"enum-types"},"Enum types"),(0,p.yg)("small",null,"Available in GraphQLite 4.0+"),(0,p.yg)("p",null,"PHP has no native support for enum types. Hopefully, there are a number of PHP libraries that emulate enums in PHP.\nThe most commonly used library is ",(0,p.yg)("a",{parentName:"p",href:"https://github.com/myclabs/php-enum"},"myclabs/php-enum")," and GraphQLite comes with\nnative support for it."),(0,p.yg)("p",null,"You will first need to install ",(0,p.yg)("a",{parentName:"p",href:"https://github.com/myclabs/php-enum"},"myclabs/php-enum"),":"),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-bash"},"$ composer require myclabs/php-enum\n")),(0,p.yg)("p",null,"Now, any class extending the ",(0,p.yg)("inlineCode",{parentName:"p"},"MyCLabs\\Enum\\Enum")," class will be mapped to a GraphQL enum:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use MyCLabs\\Enum\\Enum;\n\nclass StatusEnum extends Enum\n{\n    private const ON = 'on';\n    private const OFF = 'off';\n    private const PENDING = 'pending';\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @return User[]\n */\n#[Query]\npublic function users(StatusEnum $status): array\n{\n    if ($status == StatusEnum::ON()) {\n        // Note that the "magic" ON() method returns an instance of the StatusEnum class.\n        // Also, note that we are comparing this instance using "==" (using "===" would fail as we have 2 different instances here)\n        // ...\n    }\n    // ...\n}\n'))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"use MyCLabs\\Enum\\Enum;\n\nclass StatusEnum extends Enum\n{\n    private const ON = 'on';\n    private const OFF = 'off';\n    private const PENDING = 'pending';\n}\n")),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'/**\n * @Query\n * @return User[]\n */\npublic function users(StatusEnum $status): array\n{\n    if ($status == StatusEnum::ON()) {\n        // Note that the "magic" ON() method returns an instance of the StatusEnum class.\n        // Also, note that we are comparing this instance using "==" (using "===" would fail as we have 2 different instances here)\n        // ...\n    }\n    // ...\n}\n')))),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},"query users($status: StatusEnum!) {}\n    users(status: $status) {\n        id\n    }\n}\n")),(0,p.yg)("p",null,"By default, the name of the GraphQL enum type will be the name of the class. If you have a naming conflict (two classes\nthat live in different namespaces with the same class name), you can solve it using the ",(0,p.yg)("inlineCode",{parentName:"p"},"@EnumType")," annotation:"),(0,p.yg)(l.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,p.yg)(r.A,{value:"php8",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\EnumType;\n\n#[EnumType(name: "UserStatus")]\nclass StatusEnum extends Enum\n{\n    // ...\n}\n'))),(0,p.yg)(r.A,{value:"php7",mdxType:"TabItem"},(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Annotations\\EnumType;\n\n/**\n * @EnumType(name="UserStatus")\n */\nclass StatusEnum extends Enum\n{\n    // ...\n}\n')))),(0,p.yg)("div",{class:"alert alert--warning"},'GraphQLite must be able to find all the classes extending the "MyCLabs\\Enum" class in your project. By default, GraphQLite will look for "Enum" classes in the namespaces declared for the types. For this reason, ',(0,p.yg)("strong",null,"your enum classes MUST be in one of the namespaces declared for the types in your GraphQLite configuration file.")),(0,p.yg)("div",{class:"alert alert--info"},'There are many enumeration library in PHP and you might be using another library. If you want to add support for your own library, this is not extremely difficult to do. You need to register a custom "RootTypeMapper" with GraphQLite. You can learn more about ',(0,p.yg)("em",null,"type mappers")," in the ",(0,p.yg)("a",{href:"internals"},'"internals" documentation'),"and ",(0,p.yg)("a",{href:"https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/Root/MyCLabsEnumTypeMapper.php"},"copy/paste and adapt the root type mapper used for myclabs/php-enum"),"."),(0,p.yg)("h2",{id:"deprecation-of-fields"},"Deprecation of fields"),(0,p.yg)("p",null,"You can mark a field as deprecated in your GraphQL Schema by just annotating it with the ",(0,p.yg)("inlineCode",{parentName:"p"},"@deprecated")," PHPDoc annotation."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-php"},"namespace App\\Entities;\n\nuse TheCodingMachine\\GraphQLite\\Annotations\\Field;\nuse TheCodingMachine\\GraphQLite\\Annotations\\Type;\n\n/**\n * @Type()\n */\nclass Product\n{\n    // ...\n\n    /**\n     * @Field()\n     */\n    public function getName(): string\n    {\n        return $this->name;\n    }\n\n    /**\n     * @Field()\n     * @deprecated use field `name` instead\n     */\n    public function getProductName(): string\n    {\n        return $this->name;\n    }\n}\n")),(0,p.yg)("p",null,"This will add the ",(0,p.yg)("inlineCode",{parentName:"p"},"@deprecated")," directive to the field in the GraphQL Schema which sets the ",(0,p.yg)("inlineCode",{parentName:"p"},"isDeprecated")," field to ",(0,p.yg)("inlineCode",{parentName:"p"},"true")," and adds the reason to the ",(0,p.yg)("inlineCode",{parentName:"p"},"deprecationReason")," field in an introspection query. Fields marked as deprecated can still be queried, but will be returned in an introspection query only if ",(0,p.yg)("inlineCode",{parentName:"p"},"includeDeprecated")," is set to ",(0,p.yg)("inlineCode",{parentName:"p"},"true"),"."),(0,p.yg)("pre",null,(0,p.yg)("code",{parentName:"pre",className:"language-graphql"},'query {\n    __type(name: "Product") {\n\ufffc       fields(includeDeprecated: true) {\n\ufffc           name\n\ufffc           isDeprecated\n\ufffc           deprecationReason\n\ufffc       }\n\ufffc   }\n}\n')),(0,p.yg)("h2",{id:"more-scalar-types"},"More scalar types"),(0,p.yg)("small",null,"Available in GraphQLite 4.0+"),(0,p.yg)("p",null,'GraphQL supports "custom" scalar types. GraphQLite supports adding more GraphQL scalar types.'),(0,p.yg)("p",null,"If you need more types, you can check the ",(0,p.yg)("a",{parentName:"p",href:"https://github.com/thecodingmachine/graphqlite-misc-types"},"GraphQLite Misc. Types library"),".\nIt adds support for more scalar types out of the box in GraphQLite."),(0,p.yg)("p",null,"Or if you have some special needs, ",(0,p.yg)("a",{parentName:"p",href:"custom-types#registering-a-custom-scalar-type-advanced"},"you can develop your own scalar types"),"."))}d.isMDXComponent=!0}}]);