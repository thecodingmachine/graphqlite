"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3812],{5388:(e,a,t)=>{t.d(a,{c:()=>o});var n=t(1504),r=t(4971);const i={tabItem:"tabItem_Ymn6"};function o(e){let{children:a,hidden:t,className:o}=e;return n.createElement("div",{role:"tabpanel",className:(0,r.c)(i.tabItem,o),hidden:t},a)}},1268:(e,a,t)=>{t.d(a,{c:()=>N});var n=t(5072),r=t(1504),i=t(4971),o=t(3943),l=t(5592),s=t(632),u=t(7128),d=t(1148);function c(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:a}=e;return!!a&&"object"==typeof a&&"value"in a}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:a,label:t,attributes:n,default:r}}=e;return{value:a,label:t,attributes:n,default:r}}))}function p(e){const{values:a,children:t}=e;return(0,r.useMemo)((()=>{const e=a??c(t);return function(e){const a=(0,u.w)(e,((e,a)=>e.value===a.value));if(a.length>0)throw new Error(`Docusaurus error: Duplicate values "${a.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[a,t])}function m(e){let{value:a,tabValues:t}=e;return t.some((e=>e.value===a))}function y(e){let{queryString:a=!1,groupId:t}=e;const n=(0,l.Uz)(),i=function(e){let{queryString:a=!1,groupId:t}=e;if("string"==typeof a)return a;if(!1===a)return null;if(!0===a&&!t)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return t??null}({queryString:a,groupId:t});return[(0,s._M)(i),(0,r.useCallback)((e=>{if(!i)return;const a=new URLSearchParams(n.location.search);a.set(i,e),n.replace({...n.location,search:a.toString()})}),[i,n])]}function h(e){const{defaultValue:a,queryString:t=!1,groupId:n}=e,i=p(e),[o,l]=(0,r.useState)((()=>function(e){let{defaultValue:a,tabValues:t}=e;if(0===t.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(a){if(!m({value:a,tabValues:t}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${a}" but none of its children has the corresponding value. Available values are: ${t.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return a}const n=t.find((e=>e.default))??t[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:a,tabValues:i}))),[s,u]=y({queryString:t,groupId:n}),[c,h]=function(e){let{groupId:a}=e;const t=function(e){return e?`docusaurus.tab.${e}`:null}(a),[n,i]=(0,d.IN)(t);return[n,(0,r.useCallback)((e=>{t&&i.set(e)}),[t,i])]}({groupId:n}),g=(()=>{const e=s??c;return m({value:e,tabValues:i})?e:null})();(0,r.useLayoutEffect)((()=>{g&&l(g)}),[g]);return{selectedValue:o,selectValue:(0,r.useCallback)((e=>{if(!m({value:e,tabValues:i}))throw new Error(`Can't select invalid tab value=${e}`);l(e),u(e),h(e)}),[u,h,i]),tabValues:i}}var g=t(3664);const v={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function f(e){let{className:a,block:t,selectedValue:l,selectValue:s,tabValues:u}=e;const d=[],{blockElementScrollPositionUntilNextRender:c}=(0,o.MV)(),p=e=>{const a=e.currentTarget,t=d.indexOf(a),n=u[t].value;n!==l&&(c(a),s(n))},m=e=>{let a=null;switch(e.key){case"Enter":p(e);break;case"ArrowRight":{const t=d.indexOf(e.currentTarget)+1;a=d[t]??d[0];break}case"ArrowLeft":{const t=d.indexOf(e.currentTarget)-1;a=d[t]??d[d.length-1];break}}a?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,i.c)("tabs",{"tabs--block":t},a)},u.map((e=>{let{value:a,label:t,attributes:o}=e;return r.createElement("li",(0,n.c)({role:"tab",tabIndex:l===a?0:-1,"aria-selected":l===a,key:a,ref:e=>d.push(e),onKeyDown:m,onClick:p},o,{className:(0,i.c)("tabs__item",v.tabItem,o?.className,{"tabs__item--active":l===a})}),t??a)})))}function b(e){let{lazy:a,children:t,selectedValue:n}=e;const i=(Array.isArray(t)?t:[t]).filter(Boolean);if(a){const e=i.find((e=>e.props.value===n));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},i.map(((e,a)=>(0,r.cloneElement)(e,{key:a,hidden:e.props.value!==n}))))}function w(e){const a=h(e);return r.createElement("div",{className:(0,i.c)("tabs-container",v.tabList)},r.createElement(f,(0,n.c)({},e,a)),r.createElement(b,(0,n.c)({},e,a)))}function N(e){const a=(0,g.c)();return r.createElement(w,(0,n.c)({key:String(a)},e))}},8904:(e,a,t)=>{t.r(a),t.d(a,{assets:()=>d,contentTitle:()=>s,default:()=>y,frontMatter:()=>l,metadata:()=>u,toc:()=>c});var n=t(5072),r=(t(1504),t(5788)),i=(t(5490),t(1268)),o=t(5388);const l={id:"validation",title:"Validation",sidebar_label:"User input validation"},s=void 0,u={unversionedId:"validation",id:"version-3.0/validation",title:"Validation",description:"GraphQLite does not handle user input validation by itself. It is out of its scope.",source:"@site/versioned_docs/version-3.0/validation.mdx",sourceDirName:".",slug:"/validation",permalink:"/docs/3.0/validation",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/validation.mdx",tags:[],version:"3.0",lastUpdatedBy:"Jacob Thomason",lastUpdatedAt:1707698454,formattedLastUpdatedAt:"Feb 12, 2024",frontMatter:{id:"validation",title:"Validation",sidebar_label:"User input validation"}},d={},c=[{value:"Validating user input with Laravel",id:"validating-user-input-with-laravel",level:2},{value:"Validating user input with Symfony validator",id:"validating-user-input-with-symfony-validator",level:2},{value:"Using the Symfony validator bridge",id:"using-the-symfony-validator-bridge",level:3},{value:"Using the validator directly on a query / mutation / factory ...",id:"using-the-validator-directly-on-a-query--mutation--factory-",level:3}],p={toc:c},m="wrapper";function y(e){let{components:a,...t}=e;return(0,r.yg)(m,(0,n.c)({},p,t,{components:a,mdxType:"MDXLayout"}),(0,r.yg)("p",null,"GraphQLite does not handle user input validation by itself. It is out of its scope."),(0,r.yg)("p",null,"However, it can integrate with your favorite framework validation mechanism. The way you validate user input will\ntherefore depend on the framework you are using."),(0,r.yg)("h2",{id:"validating-user-input-with-laravel"},"Validating user input with Laravel"),(0,r.yg)("p",null,"If you are using Laravel, jump directly to the ",(0,r.yg)("a",{parentName:"p",href:"/docs/3.0/laravel-package-advanced#support-for-laravel-validation-rules"},"GraphQLite Laravel package advanced documentation"),"\nto learn how to use the Laravel validation with GraphQLite."),(0,r.yg)("h2",{id:"validating-user-input-with-symfony-validator"},"Validating user input with Symfony validator"),(0,r.yg)("p",null,"GraphQLite provides a bridge to use the ",(0,r.yg)("a",{parentName:"p",href:"https://symfony.com/doc/current/validation.html"},"Symfony validator")," directly in your application."),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"If you are using Symfony and the Symfony GraphQLite bundle, the bridge is available out of the box"),(0,r.yg)("li",{parentName:"ul"},'If you are using another framework, the "Symfony validator" component can be used in standalone mode. If you want to\nadd it to your project, you can require the ',(0,r.yg)("em",{parentName:"li"},"thecodingmachine/graphqlite-symfony-validator-bridge")," package:",(0,r.yg)("pre",{parentName:"li"},(0,r.yg)("code",{parentName:"pre",className:"language-bash"},"$ composer require thecodingmachine/graphqlite-symfony-validator-bridge\n")))),(0,r.yg)("h3",{id:"using-the-symfony-validator-bridge"},"Using the Symfony validator bridge"),(0,r.yg)("p",null,"Usually, when you use the Symfony validator component, you put annotations in your entities and you validate those entities\nusing the ",(0,r.yg)("inlineCode",{parentName:"p"},"Validator")," object."),(0,r.yg)(i.c,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(o.c,{value:"php8",mdxType:"TabItem"},(0,r.yg)("p",null,(0,r.yg)("strong",{parentName:"p"},"UserController.php")),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\Graphqlite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    #[Mutation]\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n"))),(0,r.yg)(o.c,{value:"php7",mdxType:"TabItem"},(0,r.yg)("p",null,(0,r.yg)("strong",{parentName:"p"},"UserController.php")),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\Graphqlite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    /**\n     * @Mutation\n     */\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n")))),(0,r.yg)("p",null,"Validation rules are added directly to the object in the domain model:"),(0,r.yg)(i.c,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,r.yg)(o.c,{value:"php8",mdxType:"TabItem"},(0,r.yg)("p",null,(0,r.yg)("strong",{parentName:"p"},"User.php")),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    #[Assert\\Email(message: "The email \'{{ value }}\' is not a valid email.", checkMX: true)]\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     */\n    #[Assert\\NotCompromisedPassword]\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n'))),(0,r.yg)(o.c,{value:"php7",mdxType:"TabItem"},(0,r.yg)("p",null,(0,r.yg)("strong",{parentName:"p"},"User.php")),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    /**\n     * @Assert\\Email(\n     *     message = "The email \'{{ value }}\' is not a valid email.",\n     *     checkMX = true\n     * )\n     */\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     * @Assert\\NotCompromisedPassword\n     */\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n')))),(0,r.yg)("p",null,'If a validation fails, GraphQLite will return the failed validations in the "errors" section of the JSON response:'),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-json"},'{\n  "errors": [\n    {\n      "message": "The email \'\\"foo@thisdomaindoesnotexistatall.com\\"\' is not a valid email.",\n      "extensions": {\n        "code": "bf447c1c-0266-4e10-9c6c-573df282e413",\n        "field": "email",\n        "category": "Validate"\n      }\n    }\n  ]\n}\n')),(0,r.yg)("h3",{id:"using-the-validator-directly-on-a-query--mutation--factory-"},"Using the validator directly on a query / mutation / factory ..."),(0,r.yg)("p",null,'If the data entered by the user is mapped to an object, please use the "validator" instance directly as explained in\nthe last chapter. It is a best practice to put your validation layer as close as possible to your domain model.'),(0,r.yg)("p",null,"If the data entered by the user is ",(0,r.yg)("strong",{parentName:"p"},"not")," mapped to an object, you can directly annotate your query, mutation, factory..."),(0,r.yg)("div",{class:"alert alert--warning"},"You generally don't want to do this. It is a best practice to put your validation constraints on your domain objects. Only use this technique if you want to validate user input and user input will not be stored in a domain object."),(0,r.yg)("p",null,"Use the ",(0,r.yg)("inlineCode",{parentName:"p"},"@Assertion")," annotation to validate directly the user input."),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\nuse TheCodingMachine\\Graphqlite\\Validator\\Annotations\\Assertion;\n\n/**\n * @Query\n * @Assertion(for="email", constraint=@Assert\\Email())\n */\npublic function findByMail(string $email): User\n{\n    // ...\n}\n')),(0,r.yg)("p",null,'Notice that the "constraint" parameter contains an annotation (it is an annotation wrapped in an annotation).'),(0,r.yg)("p",null,"You can also pass an array to the ",(0,r.yg)("inlineCode",{parentName:"p"},"constraint")," parameter:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'@Assertion(for="email", constraint={@Assert\\NotBlank(), @Assert\\Email()})\n')),(0,r.yg)("div",{class:"alert alert--warning"},(0,r.yg)("strong",null,"Heads up!"),' The "@Assertion" annotation is only available as a ',(0,r.yg)("strong",null,"Doctrine annotations"),". You cannot use it as a PHP 8 attributes"))}y.isMDXComponent=!0}}]);