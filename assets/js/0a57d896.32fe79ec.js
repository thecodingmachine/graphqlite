"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[7471],{19365:(e,n,t)=>{t.d(n,{A:()=>i});var r=t(96540),a=t(20053);const o={tabItem:"tabItem_Ymn6"};function i(e){let{children:n,hidden:t,className:i}=e;return r.createElement("div",{role:"tabpanel",className:(0,a.A)(o.tabItem,i),hidden:t},n)}},11470:(e,n,t)=>{t.d(n,{A:()=>w});var r=t(58168),a=t(96540),o=t(20053),i=t(23104),l=t(56347),s=t(57485),u=t(31682),c=t(89466);function p(e){return function(e){return a.Children.map(e,(e=>{if(!e||(0,a.isValidElement)(e)&&function(e){const{props:n}=e;return!!n&&"object"==typeof n&&"value"in n}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:n,label:t,attributes:r,default:a}}=e;return{value:n,label:t,attributes:r,default:a}}))}function h(e){const{values:n,children:t}=e;return(0,a.useMemo)((()=>{const e=n??p(t);return function(e){const n=(0,u.X)(e,((e,n)=>e.value===n.value));if(n.length>0)throw new Error(`Docusaurus error: Duplicate values "${n.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[n,t])}function d(e){let{value:n,tabValues:t}=e;return t.some((e=>e.value===n))}function g(e){let{queryString:n=!1,groupId:t}=e;const r=(0,l.W6)(),o=function(e){let{queryString:n=!1,groupId:t}=e;if("string"==typeof n)return n;if(!1===n)return null;if(!0===n&&!t)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return t??null}({queryString:n,groupId:t});return[(0,s.aZ)(o),(0,a.useCallback)((e=>{if(!o)return;const n=new URLSearchParams(r.location.search);n.set(o,e),r.replace({...r.location,search:n.toString()})}),[o,r])]}function y(e){const{defaultValue:n,queryString:t=!1,groupId:r}=e,o=h(e),[i,l]=(0,a.useState)((()=>function(e){let{defaultValue:n,tabValues:t}=e;if(0===t.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(n){if(!d({value:n,tabValues:t}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${n}" but none of its children has the corresponding value. Available values are: ${t.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return n}const r=t.find((e=>e.default))??t[0];if(!r)throw new Error("Unexpected error: 0 tabValues");return r.value}({defaultValue:n,tabValues:o}))),[s,u]=g({queryString:t,groupId:r}),[p,y]=function(e){let{groupId:n}=e;const t=function(e){return e?`docusaurus.tab.${e}`:null}(n),[r,o]=(0,c.Dv)(t);return[r,(0,a.useCallback)((e=>{t&&o.set(e)}),[t,o])]}({groupId:r}),m=(()=>{const e=s??p;return d({value:e,tabValues:o})?e:null})();(0,a.useLayoutEffect)((()=>{m&&l(m)}),[m]);return{selectedValue:i,selectValue:(0,a.useCallback)((e=>{if(!d({value:e,tabValues:o}))throw new Error(`Can't select invalid tab value=${e}`);l(e),u(e),y(e)}),[u,y,o]),tabValues:o}}var m=t(92303);const f={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:n,block:t,selectedValue:l,selectValue:s,tabValues:u}=e;const c=[],{blockElementScrollPositionUntilNextRender:p}=(0,i.a_)(),h=e=>{const n=e.currentTarget,t=c.indexOf(n),r=u[t].value;r!==l&&(p(n),s(r))},d=e=>{let n=null;switch(e.key){case"Enter":h(e);break;case"ArrowRight":{const t=c.indexOf(e.currentTarget)+1;n=c[t]??c[0];break}case"ArrowLeft":{const t=c.indexOf(e.currentTarget)-1;n=c[t]??c[c.length-1];break}}n?.focus()};return a.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.A)("tabs",{"tabs--block":t},n)},u.map((e=>{let{value:n,label:t,attributes:i}=e;return a.createElement("li",(0,r.A)({role:"tab",tabIndex:l===n?0:-1,"aria-selected":l===n,key:n,ref:e=>c.push(e),onKeyDown:d,onClick:h},i,{className:(0,o.A)("tabs__item",f.tabItem,i?.className,{"tabs__item--active":l===n})}),t??n)})))}function x(e){let{lazy:n,children:t,selectedValue:r}=e;const o=(Array.isArray(t)?t:[t]).filter(Boolean);if(n){const e=o.find((e=>e.props.value===r));return e?(0,a.cloneElement)(e,{className:"margin-top--md"}):null}return a.createElement("div",{className:"margin-top--md"},o.map(((e,n)=>(0,a.cloneElement)(e,{key:n,hidden:e.props.value!==r}))))}function v(e){const n=y(e);return a.createElement("div",{className:(0,o.A)("tabs-container",f.tabList)},a.createElement(b,(0,r.A)({},e,n)),a.createElement(x,(0,r.A)({},e,n)))}function w(e){const n=(0,m.A)();return a.createElement(v,(0,r.A)({key:String(n)},e))}},18419:(e,n,t)=>{t.r(n),t.d(n,{assets:()=>c,contentTitle:()=>s,default:()=>g,frontMatter:()=>l,metadata:()=>u,toc:()=>p});var r=t(58168),a=(t(96540),t(15680)),o=(t(67443),t(11470)),i=t(19365);const l={id:"error-handling",title:"Error handling",sidebar_label:"Error handling",original_id:"error-handling"},s=void 0,u={unversionedId:"error-handling",id:"version-4.1/error-handling",title:"Error handling",description:'In GraphQL, when an error occurs, the server must add an "error" entry in the response.',source:"@site/versioned_docs/version-4.1/error_handling.mdx",sourceDirName:".",slug:"/error-handling",permalink:"/docs/4.1/error-handling",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.1/error_handling.mdx",tags:[],version:"4.1",lastUpdatedBy:"Christophe Vergne",lastUpdatedAt:1715296507,formattedLastUpdatedAt:"May 9, 2024",frontMatter:{id:"error-handling",title:"Error handling",sidebar_label:"Error handling",original_id:"error-handling"},sidebar:"version-4.1/docs",previous:{title:"Inheritance and interfaces",permalink:"/docs/4.1/inheritance-interfaces"},next:{title:"User input validation",permalink:"/docs/4.1/validation"}},c={},p=[{value:"HTTP response code",id:"http-response-code",level:2},{value:"Customizing the category",id:"customizing-the-category",level:2},{value:"Customizing the extensions section",id:"customizing-the-extensions-section",level:2},{value:"Writing your own exceptions",id:"writing-your-own-exceptions",level:2},{value:"Many errors for one exception",id:"many-errors-for-one-exception",level:2},{value:"Webonyx exceptions",id:"webonyx-exceptions",level:2},{value:"Behaviour of exceptions that do not implement ClientAware",id:"behaviour-of-exceptions-that-do-not-implement-clientaware",level:2}],h={toc:p},d="wrapper";function g(e){let{components:n,...t}=e;return(0,a.yg)(d,(0,r.A)({},h,t,{components:n,mdxType:"MDXLayout"}),(0,a.yg)("p",null,'In GraphQL, when an error occurs, the server must add an "error" entry in the response.'),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-json"},'{\n  "errors": [\n    {\n      "message": "Name for character with ID 1002 could not be fetched.",\n      "locations": [ { "line": 6, "column": 7 } ],\n      "path": [ "hero", "heroFriends", 1, "name" ],\n      "extensions": {\n        "category": "Exception"\n      }\n    }\n  ]\n}\n')),(0,a.yg)("p",null,"You can generate such errors with GraphQLite by throwing a ",(0,a.yg)("inlineCode",{parentName:"p"},"GraphQLException"),"."),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},'use TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLException;\n\nthrow new GraphQLException("Exception message");\n')),(0,a.yg)("h2",{id:"http-response-code"},"HTTP response code"),(0,a.yg)("p",null,"By default, when you throw a ",(0,a.yg)("inlineCode",{parentName:"p"},"GraphQLException"),", the HTTP status code will be 500."),(0,a.yg)("p",null,"If your exception code is in the 4xx - 5xx range, the exception code will be used as an HTTP status code."),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},'// This exception will generate a HTTP 404 status code\nthrow new GraphQLException("Not found", 404);\n')),(0,a.yg)("div",{class:"alert alert--info"},"GraphQL allows to have several errors for one request. If you have several",(0,a.yg)("code",null,"GraphQLException")," thrown for the same request, the HTTP status code used will be the highest one."),(0,a.yg)("h2",{id:"customizing-the-category"},"Customizing the category"),(0,a.yg)("p",null,'By default, GraphQLite adds a "category" entry in the "extensions section". You can customize the category with the\n4th parameter of the constructor:'),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},'throw new GraphQLException("Not found", 404, null, "NOT_FOUND");\n')),(0,a.yg)("p",null,"will generate:"),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-json"},'{\n  "errors": [\n    {\n      "message": "Not found",\n      "extensions": {\n        "category": "NOT_FOUND"\n      }\n    }\n  ]\n}\n')),(0,a.yg)("h2",{id:"customizing-the-extensions-section"},"Customizing the extensions section"),(0,a.yg)("p",null,'You can customize the whole "extensions" section with the 5th parameter of the constructor:'),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"throw new GraphQLException(\"Field required\", 400, null, \"VALIDATION\", ['field' => 'name']);\n")),(0,a.yg)("p",null,"will generate:"),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-json"},'{\n  "errors": [\n    {\n      "message": "Field required",\n      "extensions": {\n        "category": "VALIDATION",\n        "field": "name"\n      }\n    }\n  ]\n}\n')),(0,a.yg)("h2",{id:"writing-your-own-exceptions"},"Writing your own exceptions"),(0,a.yg)("p",null,"Rather that throwing the base ",(0,a.yg)("inlineCode",{parentName:"p"},"GraphQLException"),", you should consider writing your own exception."),(0,a.yg)("p",null,"Any exception that implements interface ",(0,a.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLExceptionInterface"),' will be displayed\nin the GraphQL "errors" section.'),(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},'class ValidationException extends Exception implements GraphQLExceptionInterface\n{\n    /**\n     * Returns true when exception message is safe to be displayed to a client.\n     */\n    public function isClientSafe(): bool\n    {\n        return true;\n    }\n\n    /**\n     * Returns string describing a category of the error.\n     *\n     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.\n     */\n    public function getCategory(): string\n    {\n        return \'VALIDATION\';\n    }\n\n    /**\n     * Returns the "extensions" object attached to the GraphQL error.\n     *\n     * @return array<string, mixed>\n     */\n    public function getExtensions(): array\n    {\n        return [];\n    }\n}\n')),(0,a.yg)("h2",{id:"many-errors-for-one-exception"},"Many errors for one exception"),(0,a.yg)("p",null,"Sometimes, you need to display several errors in the response. But of course, at any given point in your code, you can\nthrow only one exception."),(0,a.yg)("p",null,"If you want to display several exceptions, you can bundle these exceptions in a ",(0,a.yg)("inlineCode",{parentName:"p"},"GraphQLAggregateException")," that you can\nthrow."),(0,a.yg)(o.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,a.yg)(i.A,{value:"php8",mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException;\n\n#[Query]\npublic function createProduct(string $name, float $price): Product\n{\n    $exceptions = new GraphQLAggregateException();\n\n    if ($name === '') {\n        $exceptions->add(new GraphQLException('Name cannot be empty', 400, null, 'VALIDATION'));\n    }\n    if ($price <= 0) {\n        $exceptions->add(new GraphQLException('Price must be positive', 400, null, 'VALIDATION'));\n    }\n\n    if ($exceptions->hasExceptions()) {\n        throw $exceptions;\n    }\n}\n"))),(0,a.yg)(i.A,{value:"php7",mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException;\n\n/**\n * @Query\n */\npublic function createProduct(string $name, float $price): Product\n{\n    $exceptions = new GraphQLAggregateException();\n\n    if ($name === '') {\n        $exceptions->add(new GraphQLException('Name cannot be empty', 400, null, 'VALIDATION'));\n    }\n    if ($price <= 0) {\n        $exceptions->add(new GraphQLException('Price must be positive', 400, null, 'VALIDATION'));\n    }\n\n    if ($exceptions->hasExceptions()) {\n        throw $exceptions;\n    }\n}\n")))),(0,a.yg)("h2",{id:"webonyx-exceptions"},"Webonyx exceptions"),(0,a.yg)("p",null,"GraphQLite is based on the wonderful webonyx/GraphQL-PHP library. Therefore, the Webonyx exception mechanism can\nalso be used in GraphQLite. This means you can throw a ",(0,a.yg)("inlineCode",{parentName:"p"},"GraphQL\\Error\\Error")," exception or any exception implementing\n",(0,a.yg)("a",{parentName:"p",href:"http://webonyx.github.io/graphql-php/error-handling/#errors-in-graphql"},(0,a.yg)("inlineCode",{parentName:"a"},"GraphQL\\Error\\ClientAware")," interface")),(0,a.yg)("p",null,"Actually, the ",(0,a.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLExceptionInterface")," extends Webonyx's ",(0,a.yg)("inlineCode",{parentName:"p"},"ClientAware")," interface."),(0,a.yg)("h2",{id:"behaviour-of-exceptions-that-do-not-implement-clientaware"},"Behaviour of exceptions that do not implement ClientAware"),(0,a.yg)("p",null,"If an exception that does not implement ",(0,a.yg)("inlineCode",{parentName:"p"},"ClientAware")," is thrown, by default, GraphQLite will not catch it."),(0,a.yg)("p",null,"The exception will propagate to your framework error handler/middleware that is in charge of displaying the classical error page."),(0,a.yg)("p",null,"You can ",(0,a.yg)("a",{parentName:"p",href:"http://webonyx.github.io/graphql-php/error-handling/#debugging-tools"},"change the underlying behaviour of Webonyx to catch any exception and turn them into GraphQL errors"),".\nThe way you adjust the error settings depends on the framework you are using (",(0,a.yg)("a",{parentName:"p",href:"/docs/4.1/symfony-bundle"},"Symfony"),", ",(0,a.yg)("a",{parentName:"p",href:"/docs/4.1/laravel-package"},"Laravel"),")."),(0,a.yg)("div",{class:"alert alert--info"},'To be clear: we strongly discourage changing this setting. We strongly believe that the default "RETHROW_UNSAFE_EXCEPTIONS" setting of Webonyx is the only sane setting (only putting in "errors" section exceptions designed for GraphQL).'))}g.isMDXComponent=!0}}]);