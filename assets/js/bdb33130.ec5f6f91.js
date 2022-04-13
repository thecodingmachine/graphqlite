"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[6972],{3905:function(e,n,t){t.d(n,{Zo:function(){return c},kt:function(){return d}});var a=t(67294);function r(e,n,t){return n in e?Object.defineProperty(e,n,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[n]=t,e}function l(e,n){var t=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);n&&(a=a.filter((function(n){return Object.getOwnPropertyDescriptor(e,n).enumerable}))),t.push.apply(t,a)}return t}function i(e){for(var n=1;n<arguments.length;n++){var t=null!=arguments[n]?arguments[n]:{};n%2?l(Object(t),!0).forEach((function(n){r(e,n,t[n])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(t)):l(Object(t)).forEach((function(n){Object.defineProperty(e,n,Object.getOwnPropertyDescriptor(t,n))}))}return e}function p(e,n){if(null==e)return{};var t,a,r=function(e,n){if(null==e)return{};var t,a,r={},l=Object.keys(e);for(a=0;a<l.length;a++)t=l[a],n.indexOf(t)>=0||(r[t]=e[t]);return r}(e,n);if(Object.getOwnPropertySymbols){var l=Object.getOwnPropertySymbols(e);for(a=0;a<l.length;a++)t=l[a],n.indexOf(t)>=0||Object.prototype.propertyIsEnumerable.call(e,t)&&(r[t]=e[t])}return r}var s=a.createContext({}),o=function(e){var n=a.useContext(s),t=n;return e&&(t="function"==typeof e?e(n):i(i({},n),e)),t},c=function(e){var n=o(e.components);return a.createElement(s.Provider,{value:n},e.children)},u={inlineCode:"code",wrapper:function(e){var n=e.children;return a.createElement(a.Fragment,{},n)}},m=a.forwardRef((function(e,n){var t=e.components,r=e.mdxType,l=e.originalType,s=e.parentName,c=p(e,["components","mdxType","originalType","parentName"]),m=o(t),d=r,f=m["".concat(s,".").concat(d)]||m[d]||u[d]||l;return t?a.createElement(f,i(i({ref:n},c),{},{components:t})):a.createElement(f,i({ref:n},c))}));function d(e,n){var t=arguments,r=n&&n.mdxType;if("string"==typeof e||r){var l=t.length,i=new Array(l);i[0]=m;var p={};for(var s in n)hasOwnProperty.call(n,s)&&(p[s]=n[s]);p.originalType=e,p.mdxType="string"==typeof e?e:r,i[1]=p;for(var o=2;o<l;o++)i[o]=t[o];return a.createElement.apply(null,i)}return a.createElement.apply(null,t)}m.displayName="MDXCreateElement"},58215:function(e,n,t){var a=t(67294);n.Z=function(e){var n=e.children,t=e.hidden,r=e.className;return a.createElement("div",{role:"tabpanel",hidden:t,className:r},n)}},26396:function(e,n,t){t.d(n,{Z:function(){return m}});var a=t(87462),r=t(67294),l=t(72389),i=t(79443);var p=function(){var e=(0,r.useContext)(i.Z);if(null==e)throw new Error('"useUserPreferencesContext" is used outside of "Layout" component.');return e},s=t(63616),o=t(86010),c="tabItem_vU9c";function u(e){var n,t,l,i=e.lazy,u=e.block,m=e.defaultValue,d=e.values,f=e.groupId,h=e.className,g=r.Children.map(e.children,(function(e){if((0,r.isValidElement)(e)&&void 0!==e.props.value)return e;throw new Error("Docusaurus error: Bad <Tabs> child <"+("string"==typeof e.type?e.type:e.type.name)+'>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.')})),y=null!=d?d:g.map((function(e){var n=e.props;return{value:n.value,label:n.label,attributes:n.attributes}})),v=(0,s.lx)(y,(function(e,n){return e.value===n.value}));if(v.length>0)throw new Error('Docusaurus error: Duplicate values "'+v.map((function(e){return e.value})).join(", ")+'" found in <Tabs>. Every value needs to be unique.');var b=null===m?m:null!=(n=null!=m?m:null==(t=g.find((function(e){return e.props.default})))?void 0:t.props.value)?n:null==(l=g[0])?void 0:l.props.value;if(null!==b&&!y.some((function(e){return e.value===b})))throw new Error('Docusaurus error: The <Tabs> has a defaultValue "'+b+'" but none of its children has the corresponding value. Available values are: '+y.map((function(e){return e.value})).join(", ")+". If you intend to show no default tab, use defaultValue={null} instead.");var k=p(),N=k.tabGroupChoices,T=k.setTabGroupChoices,C=(0,r.useState)(b),w=C[0],I=C[1],x=[],U=(0,s.o5)().blockElementScrollPositionUntilNextRender;if(null!=f){var P=N[f];null!=P&&P!==w&&y.some((function(e){return e.value===P}))&&I(P)}var O=function(e){var n=e.currentTarget,t=x.indexOf(n),a=y[t].value;a!==w&&(U(n),I(a),null!=f&&T(f,a))},E=function(e){var n,t=null;switch(e.key){case"ArrowRight":var a=x.indexOf(e.currentTarget)+1;t=x[a]||x[0];break;case"ArrowLeft":var r=x.indexOf(e.currentTarget)-1;t=x[r]||x[x.length-1]}null==(n=t)||n.focus()};return r.createElement("div",{className:"tabs-container"},r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.Z)("tabs",{"tabs--block":u},h)},y.map((function(e){var n=e.value,t=e.label,l=e.attributes;return r.createElement("li",(0,a.Z)({role:"tab",tabIndex:w===n?0:-1,"aria-selected":w===n,key:n,ref:function(e){return x.push(e)},onKeyDown:E,onFocus:O,onClick:O},l,{className:(0,o.Z)("tabs__item",c,null==l?void 0:l.className,{"tabs__item--active":w===n})}),null!=t?t:n)}))),i?(0,r.cloneElement)(g.filter((function(e){return e.props.value===w}))[0],{className:"margin-vert--md"}):r.createElement("div",{className:"margin-vert--md"},g.map((function(e,n){return(0,r.cloneElement)(e,{key:n,hidden:e.props.value!==w})}))))}function m(e){var n=(0,l.Z)();return r.createElement(u,(0,a.Z)({key:String(n)},e))}},23210:function(e,n,t){t.r(n),t.d(n,{contentTitle:function(){return c},default:function(){return f},frontMatter:function(){return o},metadata:function(){return u},toc:function(){return m}});var a=t(87462),r=t(63366),l=(t(67294),t(3905)),i=t(26396),p=t(58215),s=["components"],o={id:"inheritance-interfaces",title:"Inheritance and interfaces",sidebar_label:"Inheritance and interfaces"},c=void 0,u={unversionedId:"inheritance-interfaces",id:"inheritance-interfaces",title:"Inheritance and interfaces",description:"Modeling inheritance",source:"@site/docs/inheritance-interfaces.mdx",sourceDirName:".",slug:"/inheritance-interfaces",permalink:"/docs/next/inheritance-interfaces",editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/inheritance-interfaces.mdx",tags:[],version:"current",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1649831959,formattedLastUpdatedAt:"4/13/2022",frontMatter:{id:"inheritance-interfaces",title:"Inheritance and interfaces",sidebar_label:"Inheritance and interfaces"},sidebar:"docs",previous:{title:"Input types",permalink:"/docs/next/input-types"},next:{title:"Error handling",permalink:"/docs/next/error-handling"}},m=[{value:"Modeling inheritance",id:"modeling-inheritance",children:[],level:2},{value:"Mapping interfaces",id:"mapping-interfaces",children:[{value:"Implementing interfaces",id:"implementing-interfaces",children:[],level:3},{value:"Interfaces without an explicit implementing type",id:"interfaces-without-an-explicit-implementing-type",children:[],level:3}],level:2}],d={toc:m};function f(e){var n=e.components,t=(0,r.Z)(e,s);return(0,l.kt)("wrapper",(0,a.Z)({},d,t,{components:n,mdxType:"MDXLayout"}),(0,l.kt)("h2",{id:"modeling-inheritance"},"Modeling inheritance"),(0,l.kt)("p",null,"Some of your entities may extend other entities. GraphQLite will do its best to represent this hierarchy of objects in GraphQL using interfaces."),(0,l.kt)("p",null,"Let's say you have two classes, ",(0,l.kt)("inlineCode",{parentName:"p"},"Contact")," and ",(0,l.kt)("inlineCode",{parentName:"p"},"User")," (which extends ",(0,l.kt)("inlineCode",{parentName:"p"},"Contact"),"):"),(0,l.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(p.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass Contact\n{\n    // ...\n}\n\n#[Type]\nclass User extends Contact\n{\n    // ...\n}\n"))),(0,l.kt)(p.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type\n */\nclass Contact\n{\n    // ...\n}\n\n/**\n * @Type\n */\nclass User extends Contact\n{\n    // ...\n}\n")))),(0,l.kt)("p",null,"Now, let's assume you have a query that returns a contact:"),(0,l.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(p.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class ContactController\n{\n    #[Query]\n    public function getContact(): Contact\n    {\n        // ...\n    }\n}\n"))),(0,l.kt)(p.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class ContactController\n{\n    /**\n     * @Query()\n     */\n    public function getContact(): Contact\n    {\n        // ...\n    }\n}\n")))),(0,l.kt)("p",null,"When writing your GraphQL query, you are able to use fragments to retrieve fields from the ",(0,l.kt)("inlineCode",{parentName:"p"},"User")," type:"),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"contact {\n    name\n    ... User {\n       email\n    }\n}\n")),(0,l.kt)("p",null,"Written in ",(0,l.kt)("a",{parentName:"p",href:"https://graphql.org/learn/schema/#type-language"},"GraphQL type language"),", the representation of types\nwould look like this:"),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"interface ContactInterface {\n    // List of fields declared in Contact class\n}\n\ntype Contact implements ContactInterface {\n    // List of fields declared in Contact class\n}\n\ntype User implements ContactInterface {\n    // List of fields declared in Contact and User classes\n}\n")),(0,l.kt)("p",null,"Behind the scene, GraphQLite will detect that the ",(0,l.kt)("inlineCode",{parentName:"p"},"Contact")," class is extended by the ",(0,l.kt)("inlineCode",{parentName:"p"},"User")," class.\nBecause the class is extended, a GraphQL ",(0,l.kt)("inlineCode",{parentName:"p"},"ContactInterface")," interface is created dynamically."),(0,l.kt)("p",null,"The GraphQL ",(0,l.kt)("inlineCode",{parentName:"p"},"User")," type will also automatically implement this ",(0,l.kt)("inlineCode",{parentName:"p"},"ContactInterface"),". The interface contains all the fields\navailable in the ",(0,l.kt)("inlineCode",{parentName:"p"},"Contact")," type."),(0,l.kt)("h2",{id:"mapping-interfaces"},"Mapping interfaces"),(0,l.kt)("p",null,"If you want to create a pure GraphQL interface, you can also add a ",(0,l.kt)("inlineCode",{parentName:"p"},"@Type")," annotation on a PHP interface."),(0,l.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(p.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"#[Type]\ninterface UserInterface\n{\n    #[Field]\n    public function getUserName(): string;\n}\n"))),(0,l.kt)(p.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type\n */\ninterface UserInterface\n{\n    /**\n     * @Field\n     */\n    public function getUserName(): string;\n}\n")))),(0,l.kt)("p",null,"This will automatically create a GraphQL interface whose description is:"),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"interface UserInterface {\n    userName: String!\n}\n")),(0,l.kt)("h3",{id:"implementing-interfaces"},"Implementing interfaces"),(0,l.kt)("p",null,'You don\'t have to do anything special to implement an interface in your GraphQL types.\nSimply "implement" the interface in PHP and you are done!'),(0,l.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(p.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"#[Type]\nclass User implements UserInterface\n{\n    public function getUserName(): string;\n}\n"))),(0,l.kt)(p.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * @Type\n */\nclass User implements UserInterface\n{\n    public function getUserName(): string;\n}\n")))),(0,l.kt)("p",null,"This will translate in GraphQL schema as:"),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"interface UserInterface {\n    userName: String!\n}\n\ntype User implements UserInterface {\n    userName: String!\n}\n")),(0,l.kt)("p",null,"Please note that you do not need to put the ",(0,l.kt)("inlineCode",{parentName:"p"},"@Field")," annotation again in the implementing class."),(0,l.kt)("h3",{id:"interfaces-without-an-explicit-implementing-type"},"Interfaces without an explicit implementing type"),(0,l.kt)("p",null,"You don't have to explicitly put a ",(0,l.kt)("inlineCode",{parentName:"p"},"@Type")," annotation on the class implementing the interface (though this\nis usually a good idea)."),(0,l.kt)(i.Z,{defaultValue:"php8",values:[{label:"PHP 8",value:"php8"},{label:"PHP 7",value:"php7"}],mdxType:"Tabs"},(0,l.kt)(p.Z,{value:"php8",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * Look, this class has no #Type attribute\n */\nclass User implements UserInterface\n{\n    public function getUserName(): string;\n}\n")),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class UserController\n{\n    #[Query]\n    public function getUser(): UserInterface // This will work!\n    {\n        // ...\n    }\n}\n"))),(0,l.kt)(p.Z,{value:"php7",mdxType:"TabItem"},(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"/**\n * Look, this class has no @Type annotation\n */\nclass User implements UserInterface\n{\n    public function getUserName(): string;\n}\n")),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-php"},"class UserController\n{\n    /**\n     * @Query()\n     */\n    public function getUser(): UserInterface // This will work!\n    {\n        // ...\n    }\n}\n")))),(0,l.kt)("div",{class:"alert alert--info"},'If GraphQLite cannot find a proper GraphQL Object type implementing an interface, it will create an object type "on the fly".'),(0,l.kt)("p",null,"In the example above, because the ",(0,l.kt)("inlineCode",{parentName:"p"},"User")," class has no ",(0,l.kt)("inlineCode",{parentName:"p"},"@Type")," annotations, GraphQLite will\ncreate a ",(0,l.kt)("inlineCode",{parentName:"p"},"UserImpl")," type that implements ",(0,l.kt)("inlineCode",{parentName:"p"},"UserInterface"),"."),(0,l.kt)("pre",null,(0,l.kt)("code",{parentName:"pre",className:"language-graphql"},"interface UserInterface {\n    userName: String!\n}\n\ntype UserImpl implements UserInterface {\n    userName: String!\n}\n")))}f.isMDXComponent=!0}}]);