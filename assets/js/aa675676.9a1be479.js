"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5075],{3905:(e,t,a)=>{a.d(t,{Zo:()=>d,kt:()=>m});var n=a(67294);function r(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}function i(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function o(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?i(Object(a),!0).forEach((function(t){r(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):i(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}function l(e,t){if(null==e)return{};var a,n,r=function(e,t){if(null==e)return{};var a,n,r={},i=Object.keys(e);for(n=0;n<i.length;n++)a=i[n],t.indexOf(a)>=0||(r[a]=e[a]);return r}(e,t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);for(n=0;n<i.length;n++)a=i[n],t.indexOf(a)>=0||Object.prototype.propertyIsEnumerable.call(e,a)&&(r[a]=e[a])}return r}var s=n.createContext({}),u=function(e){var t=n.useContext(s),a=t;return e&&(a="function"==typeof e?e(t):o(o({},t),e)),a},d=function(e){var t=u(e.components);return n.createElement(s.Provider,{value:t},e.children)},p={inlineCode:"code",wrapper:function(e){var t=e.children;return n.createElement(n.Fragment,{},t)}},c=n.forwardRef((function(e,t){var a=e.components,r=e.mdxType,i=e.originalType,s=e.parentName,d=l(e,["components","mdxType","originalType","parentName"]),c=u(a),m=r,h=c["".concat(s,".").concat(m)]||c[m]||p[m]||i;return a?n.createElement(h,o(o({ref:t},d),{},{components:a})):n.createElement(h,o({ref:t},d))}));function m(e,t){var a=arguments,r=t&&t.mdxType;if("string"==typeof e||r){var i=a.length,o=new Array(i);o[0]=c;var l={};for(var s in t)hasOwnProperty.call(t,s)&&(l[s]=t[s]);l.originalType=e,l.mdxType="string"==typeof e?e:r,o[1]=l;for(var u=2;u<i;u++)o[u]=a[u];return n.createElement.apply(null,o)}return n.createElement.apply(null,a)}c.displayName="MDXCreateElement"},85162:(e,t,a)=>{a.d(t,{Z:()=>o});var n=a(67294),r=a(86010);const i="tabItem_Ymn6";function o(e){let{children:t,hidden:a,className:o}=e;return n.createElement("div",{role:"tabpanel",className:(0,r.Z)(i,o),hidden:a},t)}},65488:(e,t,a)=>{a.d(t,{Z:()=>m});var n=a(87462),r=a(67294),i=a(86010),o=a(72389),l=a(67392),s=a(7094),u=a(12466);const d="tabList__CuJ",p="tabItem_LNqP";function c(e){var t,a;const{lazy:o,block:c,defaultValue:m,values:h,groupId:v,className:y}=e,f=r.Children.map(e.children,(e=>{if((0,r.isValidElement)(e)&&"value"in e.props)return e;throw new Error("Docusaurus error: Bad <Tabs> child <"+("string"==typeof e.type?e.type:e.type.name)+'>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.')})),g=null!=h?h:f.map((e=>{let{props:{value:t,label:a,attributes:n}}=e;return{value:t,label:a,attributes:n}})),b=(0,l.l)(g,((e,t)=>e.value===t.value));if(b.length>0)throw new Error('Docusaurus error: Duplicate values "'+b.map((e=>e.value)).join(", ")+'" found in <Tabs>. Every value needs to be unique.');const k=null===m?m:null!=(t=null!=m?m:null==(a=f.find((e=>e.props.default)))?void 0:a.props.value)?t:f[0].props.value;if(null!==k&&!g.some((e=>e.value===k)))throw new Error('Docusaurus error: The <Tabs> has a defaultValue "'+k+'" but none of its children has the corresponding value. Available values are: '+g.map((e=>e.value)).join(", ")+". If you intend to show no default tab, use defaultValue={null} instead.");const{tabGroupChoices:w,setTabGroupChoices:N}=(0,s.U)(),[T,$]=(0,r.useState)(k),x=[],{blockElementScrollPositionUntilNextRender:E}=(0,u.o5)();if(null!=v){const e=w[v];null!=e&&e!==T&&g.some((t=>t.value===e))&&$(e)}const C=e=>{const t=e.currentTarget,a=x.indexOf(t),n=g[a].value;n!==T&&(E(t),$(n),null!=v&&N(v,String(n)))},V=e=>{var t;let a=null;switch(e.key){case"ArrowRight":{var n;const t=x.indexOf(e.currentTarget)+1;a=null!=(n=x[t])?n:x[0];break}case"ArrowLeft":{var r;const t=x.indexOf(e.currentTarget)-1;a=null!=(r=x[t])?r:x[x.length-1];break}}null==(t=a)||t.focus()};return r.createElement("div",{className:(0,i.Z)("tabs-container",d)},r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,i.Z)("tabs",{"tabs--block":c},y)},g.map((e=>{let{value:t,label:a,attributes:o}=e;return r.createElement("li",(0,n.Z)({role:"tab",tabIndex:T===t?0:-1,"aria-selected":T===t,key:t,ref:e=>x.push(e),onKeyDown:V,onFocus:C,onClick:C},o,{className:(0,i.Z)("tabs__item",p,null==o?void 0:o.className,{"tabs__item--active":T===t})}),null!=a?a:t)}))),o?(0,r.cloneElement)(f.filter((e=>e.props.value===T))[0],{className:"margin-top--md"}):r.createElement("div",{className:"margin-top--md"},f.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==T})))))}function m(e){const t=(0,o.Z)();return r.createElement(c,(0,n.Z)({key:String(t)},e))}},90765:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>d,contentTitle:()=>s,default:()=>m,frontMatter:()=>l,metadata:()=>u,toc:()=>p});var n=a(87462),r=(a(67294),a(3905)),i=a(65488),o=a(85162);const l={id:"validation",title:"Validation",sidebar_label:"User input validation",original_id:"validation"},s=void 0,u={unversionedId:"validation",id:"version-4.1/validation",title:"Validation",description:"GraphQLite does not handle user input validation by itself. It is out of its scope.",source:"@site/versioned_docs/version-4.1/validation.mdx",sourceDirName:".",slug:"/validation",permalink:"/docs/4.1/validation",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.1/validation.mdx",tags:[],version:"4.1",lastUpdatedBy:"bladl",lastUpdatedAt:1657811576,formattedLastUpdatedAt:"7/14/2022",frontMatter:{id:"validation",title:"Validation",sidebar_label:"User input validation",original_id:"validation"},sidebar:"version-4.1/docs",previous:{title:"Error handling",permalink:"/docs/4.1/error-handling"},next:{title:"Authentication and authorization",permalink:"/docs/4.1/authentication_authorization"}},d={},p=[{value:"Validating user input with Laravel",id:"validating-user-input-with-laravel",level:2},{value:"Validating user input with Symfony validator",id:"validating-user-input-with-symfony-validator",level:2},{value:"Using the Symfony validator bridge",id:"using-the-symfony-validator-bridge",level:3},{value:"Using the validator directly on a query / mutation / factory ...",id:"using-the-validator-directly-on-a-query--mutation--factory-",level:3}],c={toc:p};function m(e){let{components:t,...a}=e;return(0,r.kt)("wrapper",(0,n.Z)({},c,a,{components:t,mdxType:"MDXLayout"}),(0,r.kt)("p",null,"GraphQLite does not handle user input validation by itself. It is out of its scope."),(0,r.kt)("p",null,"However, it can integrate with your favorite framework validation mechanism. The way you validate user input will\ntherefore depend on the framework you are using."),(0,r.kt)("h2",{id:"validating-user-input-with-laravel"},"Validating user input with Laravel"),(0,r.kt)("p",null,"If you are using Laravel, jump directly to the ",(0,r.kt)("a",{parentName:"p",href:"/docs/4.1/laravel-package-advanced#support-for-laravel-validation-rules"},"GraphQLite Laravel package advanced documentation"),"\nto learn how to use the Laravel validation with GraphQLite."),(0,r.kt)("h2",{id:"validating-user-input-with-symfony-validator"},"Validating user input with Symfony validator"),(0,r.kt)("p",null,"GraphQLite provides a bridge to use the ",(0,r.kt)("a",{parentName:"p",href:"https://symfony.com/doc/current/validation.html"},"Symfony validator")," directly in your application."),(0,r.kt)("ul",null,(0,r.kt)("li",{parentName:"ul"},"If you are using Symfony and the Symfony GraphQLite bundle, the bridge is available out of the box"),(0,r.kt)("li",{parentName:"ul"},'If you are using another framework, the "Symfony validator" component can be used in standalone mode. If you want to\nadd it to your project, you can require the ',(0,r.kt)("em",{parentName:"li"},"thecodingmachine/graphqlite-symfony-validator-bridge")," package:",(0,r.kt)("pre",{parentName:"li"},(0,r.kt)("code",{parentName:"pre",className:"language-bash"},"$ composer require thecodingmachine/graphqlite-symfony-validator-bridge\n")))),(0,r.kt)("h3",{id:"using-the-symfony-validator-bridge"},"Using the Symfony validator bridge"),(0,r.kt)("p",null,"Usually, when you use the Symfony validator component, you put annotations in your entities and you validate those entities\nusing the ",(0,r.kt)("inlineCode",{parentName:"p"},"Validator")," object."),(0,r.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.kt)(o.Z,{value:"php8",mdxType:"TabItem"},(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"UserController.php")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\Graphqlite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    #[Mutation]\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n"))),(0,r.kt)(o.Z,{value:"php7",mdxType:"TabItem"},(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"UserController.php")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\Graphqlite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    /**\n     * @Mutation\n     */\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n")))),(0,r.kt)("p",null,"Validation rules are added directly to the object in the domain model:"),(0,r.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.kt)(o.Z,{value:"php8",mdxType:"TabItem"},(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"User.php")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    #[Assert\\Email(message: "The email \'{{ value }}\' is not a valid email.", checkMX: true)]\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     */\n    #[Assert\\NotCompromisedPassword]\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n'))),(0,r.kt)(o.Z,{value:"php7",mdxType:"TabItem"},(0,r.kt)("p",null,(0,r.kt)("strong",{parentName:"p"},"User.php")),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    /**\n     * @Assert\\Email(\n     *     message = "The email \'{{ value }}\' is not a valid email.",\n     *     checkMX = true\n     * )\n     */\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     * @Assert\\NotCompromisedPassword\n     */\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n')))),(0,r.kt)("p",null,'If a validation fails, GraphQLite will return the failed validations in the "errors" section of the JSON response:'),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-json"},'{\n  "errors": [\n    {\n      "message": "The email \'\\"foo@thisdomaindoesnotexistatall.com\\"\' is not a valid email.",\n      "extensions": {\n        "code": "bf447c1c-0266-4e10-9c6c-573df282e413",\n        "field": "email",\n        "category": "Validate"\n      }\n    }\n  ]\n}\n')),(0,r.kt)("h3",{id:"using-the-validator-directly-on-a-query--mutation--factory-"},"Using the validator directly on a query / mutation / factory ..."),(0,r.kt)("p",null,'If the data entered by the user is mapped to an object, please use the "validator" instance directly as explained in\nthe last chapter. It is a best practice to put your validation layer as close as possible to your domain model.'),(0,r.kt)("p",null,"If the data entered by the user is ",(0,r.kt)("strong",{parentName:"p"},"not")," mapped to an object, you can directly annotate your query, mutation, factory..."),(0,r.kt)("div",{class:"alert alert--warning"},"You generally don't want to do this. It is a best practice to put your validation constraints on your domain objects. Only use this technique if you want to validate user input and user input will not be stored in a domain object."),(0,r.kt)("p",null,"Use the ",(0,r.kt)("inlineCode",{parentName:"p"},"@Assertion")," annotation to validate directly the user input."),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\nuse TheCodingMachine\\Graphqlite\\Validator\\Annotations\\Assertion;\n\n/**\n * @Query\n * @Assertion(for="email", constraint=@Assert\\Email())\n */\npublic function findByMail(string $email): User\n{\n    // ...\n}\n')),(0,r.kt)("p",null,'Notice that the "constraint" parameter contains an annotation (it is an annotation wrapped in an annotation).'),(0,r.kt)("p",null,"You can also pass an array to the ",(0,r.kt)("inlineCode",{parentName:"p"},"constraint")," parameter:"),(0,r.kt)("pre",null,(0,r.kt)("code",{parentName:"pre",className:"language-php"},'@Assertion(for="email", constraint={@Assert\\NotBlank(), @Assert\\Email()})\n')),(0,r.kt)("div",{class:"alert alert--warning"},(0,r.kt)("strong",null,"Heads up!"),' The "@Assertion" annotation is only available as a ',(0,r.kt)("strong",null,"Doctrine annotations"),". You cannot use it as a PHP 8 attributes"))}m.isMDXComponent=!0}}]);