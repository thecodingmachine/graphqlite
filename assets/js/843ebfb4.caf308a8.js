"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1585],{9365:(e,t,a)=>{a.d(t,{A:()=>o});var n=a(6540),i=a(53);const r={tabItem:"tabItem_Ymn6"};function o(e){let{children:t,hidden:a,className:o}=e;return n.createElement("div",{role:"tabpanel",className:(0,i.A)(r.tabItem,o),hidden:a},t)}},1470:(e,t,a)=>{a.d(t,{A:()=>N});var n=a(8168),i=a(6540),r=a(53),o=a(3104),l=a(6347),s=a(7485),u=a(1682),d=a(9466);function p(e){return function(e){return i.Children.map(e,(e=>{if(!e||(0,i.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:a,attributes:n,default:i}}=e;return{value:t,label:a,attributes:n,default:i}}))}function c(e){const{values:t,children:a}=e;return(0,i.useMemo)((()=>{const e=t??p(a);return function(e){const t=(0,u.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,a])}function y(e){let{value:t,tabValues:a}=e;return a.some((e=>e.value===t))}function h(e){let{queryString:t=!1,groupId:a}=e;const n=(0,l.W6)(),r=function(e){let{queryString:t=!1,groupId:a}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:t,groupId:a});return[(0,s.aZ)(r),(0,i.useCallback)((e=>{if(!r)return;const t=new URLSearchParams(n.location.search);t.set(r,e),n.replace({...n.location,search:t.toString()})}),[r,n])]}function m(e){const{defaultValue:t,queryString:a=!1,groupId:n}=e,r=c(e),[o,l]=(0,i.useState)((()=>function(e){let{defaultValue:t,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!y({value:t,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const n=a.find((e=>e.default))??a[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:t,tabValues:r}))),[s,u]=h({queryString:a,groupId:n}),[p,m]=function(e){let{groupId:t}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(t),[n,r]=(0,d.Dv)(a);return[n,(0,i.useCallback)((e=>{a&&r.set(e)}),[a,r])]}({groupId:n}),g=(()=>{const e=s??p;return y({value:e,tabValues:r})?e:null})();(0,i.useLayoutEffect)((()=>{g&&l(g)}),[g]);return{selectedValue:o,selectValue:(0,i.useCallback)((e=>{if(!y({value:e,tabValues:r}))throw new Error(`Can't select invalid tab value=${e}`);l(e),u(e),m(e)}),[u,m,r]),tabValues:r}}var g=a(2303);const v={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function f(e){let{className:t,block:a,selectedValue:l,selectValue:s,tabValues:u}=e;const d=[],{blockElementScrollPositionUntilNextRender:p}=(0,o.a_)(),c=e=>{const t=e.currentTarget,a=d.indexOf(t),n=u[a].value;n!==l&&(p(t),s(n))},y=e=>{let t=null;switch(e.key){case"Enter":c(e);break;case"ArrowRight":{const a=d.indexOf(e.currentTarget)+1;t=d[a]??d[0];break}case"ArrowLeft":{const a=d.indexOf(e.currentTarget)-1;t=d[a]??d[d.length-1];break}}t?.focus()};return i.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,r.A)("tabs",{"tabs--block":a},t)},u.map((e=>{let{value:t,label:a,attributes:o}=e;return i.createElement("li",(0,n.A)({role:"tab",tabIndex:l===t?0:-1,"aria-selected":l===t,key:t,ref:e=>d.push(e),onKeyDown:y,onClick:c},o,{className:(0,r.A)("tabs__item",v.tabItem,o?.className,{"tabs__item--active":l===t})}),a??t)})))}function b(e){let{lazy:t,children:a,selectedValue:n}=e;const r=(Array.isArray(a)?a:[a]).filter(Boolean);if(t){const e=r.find((e=>e.props.value===n));return e?(0,i.cloneElement)(e,{className:"margin-top--md"}):null}return i.createElement("div",{className:"margin-top--md"},r.map(((e,t)=>(0,i.cloneElement)(e,{key:t,hidden:e.props.value!==n}))))}function w(e){const t=m(e);return i.createElement("div",{className:(0,r.A)("tabs-container",v.tabList)},i.createElement(f,(0,n.A)({},e,t)),i.createElement(b,(0,n.A)({},e,t)))}function N(e){const t=(0,g.A)();return i.createElement(w,(0,n.A)({key:String(t)},e))}},237:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>d,contentTitle:()=>s,default:()=>h,frontMatter:()=>l,metadata:()=>u,toc:()=>p});var n=a(8168),i=(a(6540),a(5680)),r=(a(7443),a(1470)),o=a(9365);const l={id:"validation",title:"Validation",sidebar_label:"User input validation"},s=void 0,u={unversionedId:"validation",id:"validation",title:"Validation",description:"GraphQLite does not handle user input validation by itself. It is out of its scope.",source:"@site/docs/validation.mdx",sourceDirName:".",slug:"/validation",permalink:"/docs/next/validation",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/validation.mdx",tags:[],version:"current",lastUpdatedBy:"Yurii",lastUpdatedAt:1710904486,formattedLastUpdatedAt:"Mar 20, 2024",frontMatter:{id:"validation",title:"Validation",sidebar_label:"User input validation"},sidebar:"docs",previous:{title:"Error handling",permalink:"/docs/next/error-handling"},next:{title:"Authentication and authorization",permalink:"/docs/next/authentication-authorization"}},d={},p=[{value:"Validating user input with Laravel",id:"validating-user-input-with-laravel",level:2},{value:"Validating user input with Symfony validator",id:"validating-user-input-with-symfony-validator",level:2},{value:"Using the Symfony validator bridge",id:"using-the-symfony-validator-bridge",level:3},{value:"Using the validator directly on a query / mutation / subscription / factory ...",id:"using-the-validator-directly-on-a-query--mutation--subscription--factory-",level:3},{value:"Custom InputType Validation",id:"custom-inputtype-validation",level:2}],c={toc:p},y="wrapper";function h(e){let{components:t,...a}=e;return(0,i.yg)(y,(0,n.A)({},c,a,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"GraphQLite does not handle user input validation by itself. It is out of its scope."),(0,i.yg)("p",null,"However, it can integrate with your favorite framework validation mechanism. The way you validate user input will\ntherefore depend on the framework you are using."),(0,i.yg)("h2",{id:"validating-user-input-with-laravel"},"Validating user input with Laravel"),(0,i.yg)("p",null,"If you are using Laravel, jump directly to the ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/laravel-package-advanced#support-for-laravel-validation-rules"},"GraphQLite Laravel package advanced documentation"),"\nto learn how to use the Laravel validation with GraphQLite."),(0,i.yg)("h2",{id:"validating-user-input-with-symfony-validator"},"Validating user input with Symfony validator"),(0,i.yg)("p",null,"GraphQLite provides a bridge to use the ",(0,i.yg)("a",{parentName:"p",href:"https://symfony.com/doc/current/validation.html"},"Symfony validator")," directly in your application."),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("p",{parentName:"li"},"If you are using Symfony and the Symfony GraphQLite bundle, the bridge is available out of the box")),(0,i.yg)("li",{parentName:"ul"},(0,i.yg)("p",{parentName:"li"},'If you are using another framework, the "Symfony validator" component can be used in standalone mode. If you want to\nadd it to your project, you can require the ',(0,i.yg)("em",{parentName:"p"},"thecodingmachine/graphqlite-symfony-validator-bridge")," package:"),(0,i.yg)("pre",{parentName:"li"},(0,i.yg)("code",{parentName:"pre",className:"language-bash"},"$ composer require thecodingmachine/graphqlite-symfony-validator-bridge\n")))),(0,i.yg)("h3",{id:"using-the-symfony-validator-bridge"},"Using the Symfony validator bridge"),(0,i.yg)("p",null,"Usually, when you use the Symfony validator component, you put annotations in your entities and you validate those entities\nusing the ",(0,i.yg)("inlineCode",{parentName:"p"},"Validator")," object."),(0,i.yg)(r.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(o.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="UserController.php"',title:'"UserController.php"'},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\GraphQLite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    #[Mutation]\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n"))),(0,i.yg)(o.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="UserController.php"',title:'"UserController.php"'},"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\nuse TheCodingMachine\\GraphQLite\\Validator\\ValidationFailedException\n\nclass UserController\n{\n    private $validator;\n\n    public function __construct(ValidatorInterface $validator)\n    {\n        $this->validator = $validator;\n    }\n\n    /**\n     * @Mutation\n     */\n    public function createUser(string $email, string $password): User\n    {\n        $user = new User($email, $password);\n\n        // Let's validate the user\n        $errors = $this->validator->validate($user);\n\n        // Throw an appropriate GraphQL exception if validation errors are encountered\n        ValidationFailedException::throwException($errors);\n\n        // No errors? Let's continue and save the user\n        // ...\n    }\n}\n")))),(0,i.yg)("p",null,"Validation rules are added directly to the object in the domain model:"),(0,i.yg)(r.A,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,i.yg)(o.A,{value:"php8",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="User.php"',title:'"User.php"'},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    #[Assert\\Email(message: "The email \'{{ value }}\' is not a valid email.", checkMX: true)]\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     */\n    #[Assert\\NotCompromisedPassword]\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n'))),(0,i.yg)(o.A,{value:"php7",mdxType:"TabItem"},(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php",metastring:'title="User.php"',title:'"User.php"'},'use Symfony\\Component\\Validator\\Constraints as Assert;\n\nclass User\n{\n    /**\n     * @Assert\\Email(\n     *     message = "The email \'{{ value }}\' is not a valid email.",\n     *     checkMX = true\n     * )\n     */\n    private $email;\n\n    /**\n     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.\n     * @Assert\\NotCompromisedPassword\n     */\n    private $password;\n\n    public function __construct(string $email, string $password)\n    {\n        $this->email = $email;\n        $this->password = $password;\n    }\n\n    // ...\n}\n')))),(0,i.yg)("p",null,'If a validation fails, GraphQLite will return the failed validations in the "errors" section of the JSON response:'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-json"},'{\n    "errors": [\n        {\n            "message": "The email \'\\"foo@thisdomaindoesnotexistatall.com\\"\' is not a valid email.",\n            "extensions": {\n                "code": "bf447c1c-0266-4e10-9c6c-573df282e413",\n                "field": "email",\n                "category": "Validate"\n            }\n        }\n    ]\n}\n')),(0,i.yg)("h3",{id:"using-the-validator-directly-on-a-query--mutation--subscription--factory-"},"Using the validator directly on a query / mutation / subscription / factory ..."),(0,i.yg)("p",null,'If the data entered by the user is mapped to an object, please use the "validator" instance directly as explained in\nthe last chapter. It is a best practice to put your validation layer as close as possible to your domain model.'),(0,i.yg)("p",null,"If the data entered by the user is ",(0,i.yg)("strong",{parentName:"p"},"not")," mapped to an object, you can directly annotate your query, mutation, factory..."),(0,i.yg)("div",{class:"alert alert--warning"},"You generally don't want to do this. It is a best practice to put your validation constraints on your domain objects. Only use this technique if you want to validate user input and user input will not be stored in a domain object."),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"@Assertion")," annotation to validate directly the user input."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'use Symfony\\Component\\Validator\\Constraints as Assert;\nuse TheCodingMachine\\GraphQLite\\Validator\\Annotations\\Assertion;\n\n/**\n * @Query\n * @Assertion(for="email", constraint=@Assert\\Email())\n */\npublic function findByMail(string $email): User\n{\n    // ...\n}\n')),(0,i.yg)("p",null,'Notice that the "constraint" parameter contains an annotation (it is an annotation wrapped in an annotation).'),(0,i.yg)("p",null,"You can also pass an array to the ",(0,i.yg)("inlineCode",{parentName:"p"},"constraint")," parameter:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'@Assertion(for="email", constraint={@Assert\\NotBlank(), @Assert\\Email()})\n')),(0,i.yg)("div",{class:"alert alert--warning"},(0,i.yg)("strong",null,"Heads up!"),' The "@Assertion" annotation is only available as a ',(0,i.yg)("strong",null,"Doctrine annotations"),". You cannot use it as a PHP 8 attributes"),(0,i.yg)("h2",{id:"custom-inputtype-validation"},"Custom InputType Validation"),(0,i.yg)("p",null,"GraphQLite also supports a fully custom validation implementation for all input types defined with an ",(0,i.yg)("inlineCode",{parentName:"p"},"@Input")," annotation or PHP8 ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Input]")," attribute.  This offers a way to validate input types before they're available as a method parameter of your query and mutation controllers.  This way, when you're using your query or mutation controllers, you can feel confident that your input type objects have already been validated."),(0,i.yg)("div",{class:"alert alert--warning"},(0,i.yg)("p",null,"It's important to note that this validation implementation does not validate input types created with a factory.  If you are creating an input type with a factory, or using primitive parameters in your query/mutation controllers, you should be sure to validate these independently.  This is strictly for input type objects."),(0,i.yg)("p",null,"You can use one of the framework validation libraries listed above or implement your own validation for these cases.  If you're using input type objects for most all of your query and mutation controllers, then there is little additional validation concerns with regards to user input.  There are many reasons why you should consider defaulting to an InputType object, as opposed to individual arguments, for your queries and mutations.  This is just one additional perk.")),(0,i.yg)("p",null,"To get started with validation on input types defined by an ",(0,i.yg)("inlineCode",{parentName:"p"},"@Input")," annotation, you'll first need to register your validator with the ",(0,i.yg)("inlineCode",{parentName:"p"},"SchemaFactory"),"."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"$factory = new SchemaFactory($cache, $this->container);\n$factory->addControllerNamespace('App\\\\Controllers');\n$factory->addTypeNamespace('App');\n// Register your validator\n$factory->setInputTypeValidator($this->container->get('your_validator'));\n$factory->createSchema();\n")),(0,i.yg)("p",null,"Your input type validator must implement the ",(0,i.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Types\\InputTypeValidatorInterface"),", as shown below:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"interface InputTypeValidatorInterface\n{\n    /**\n     * Checks to see if the Validator is currently enabled.\n     */\n    public function isEnabled(): bool;\n\n    /**\n     * Performs the validation of the InputType.\n     *\n     * @param object $input     The input type object to validate\n     */\n    public function validate(object $input): void;\n}\n")),(0,i.yg)("p",null,"The interface is quite simple.  Handle all of your own validation logic in the ",(0,i.yg)("inlineCode",{parentName:"p"},"validate")," method.  For example, you might use Symfony's annotation based validation in addition to some other custom validation logic.  It's really up to you on how you wish to handle your own validation.  The ",(0,i.yg)("inlineCode",{parentName:"p"},"validate")," method will receive the input type object populated with the user input."),(0,i.yg)("p",null,"You'll notice that the ",(0,i.yg)("inlineCode",{parentName:"p"},"validate")," method has a ",(0,i.yg)("inlineCode",{parentName:"p"},"void")," return.  The purpose here is to encourage you to throw an Exception or handle validation output however you best see fit.  GraphQLite does it's best to stay out of your way and doesn't make attempts to handle validation output.  You can, however, throw an instance of ",(0,i.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLException")," or ",(0,i.yg)("inlineCode",{parentName:"p"},"TheCodingMachine\\GraphQLite\\Exceptions\\GraphQLAggregateException")," as usual (see ",(0,i.yg)("a",{parentName:"p",href:"error-handling"},"Error Handling")," for more details)."),(0,i.yg)("p",null,"Also available is the ",(0,i.yg)("inlineCode",{parentName:"p"},"isEnabled")," method.  This method is checked before executing validation on an InputType being resolved.  You can work out your own logic to selectively enable or disable validation through this method.  In most cases, you can simply return ",(0,i.yg)("inlineCode",{parentName:"p"},"true")," to keep it always enabled."),(0,i.yg)("p",null,"And that's it, now, anytime an input type is resolved, the validator will be executed on that input type immediately after it has been hydrated with user input."))}h.isMDXComponent=!0}}]);