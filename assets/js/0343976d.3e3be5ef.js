"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3765],{36664:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>o,default:()=>p,frontMatter:()=>a,metadata:()=>s,toc:()=>u});var i=n(58168),r=(n(96540),n(15680));n(67443);const a={id:"implementing-security",title:"Connecting GraphQLite to your framework's security module",sidebar_label:"Connecting security to your framework",original_id:"implementing-security"},o=void 0,s={unversionedId:"implementing-security",id:"version-4.0/implementing-security",title:"Connecting GraphQLite to your framework's security module",description:"This step is NOT necessary for users using GraphQLite through the Symfony Bundle or the Laravel package",source:"@site/versioned_docs/version-4.0/implementing-security.md",sourceDirName:".",slug:"/implementing-security",permalink:"/docs/4.0/implementing-security",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/implementing-security.md",tags:[],version:"4.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1728277176,formattedLastUpdatedAt:"Oct 7, 2024",frontMatter:{id:"implementing-security",title:"Connecting GraphQLite to your framework's security module",sidebar_label:"Connecting security to your framework",original_id:"implementing-security"},sidebar:"version-4.0/docs",previous:{title:"Fine grained security",permalink:"/docs/4.0/fine-grained-security"},next:{title:"Query plan",permalink:"/docs/4.0/query-plan"}},c={},u=[],l={toc:u},g="wrapper";function p(e){let{components:t,...n}=e;return(0,r.yg)(g,(0,i.A)({},l,n,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("div",{class:"alert alert--info"},"This step is NOT necessary for users using GraphQLite through the Symfony Bundle or the Laravel package"),(0,r.yg)("p",null,"GraphQLite needs to know if a user is logged or not, and what rights it has.\nBut this is specific of the framework you use."),(0,r.yg)("p",null,"To plug GraphQLite to your framework's security mechanism, you will have to provide two classes implementing:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthenticationServiceInterface")),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthorizationServiceInterface"))),(0,r.yg)("p",null,"Those two interfaces act as adapters between GraphQLite and your framework:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthenticationServiceInterface\n{\n    /**\n     * Returns true if the "current" user is logged\n     */\n    public function isLogged(): bool;\n\n    /**\n     * Returns an object representing the current logged user.\n     * Can return null if the user is not logged.\n     */\n    public function getUser(): ?object;\n}\n')),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthorizationServiceInterface\n{\n    /**\n     * Returns true if the "current" user has access to the right "$right"\n     *\n     * @param mixed $subject The scope this right applies on. $subject is typically an object or a FQCN. Set $subject to "null" if the right is global.\n     */\n    public function isAllowed(string $right, $subject = null): bool;\n}\n')),(0,r.yg)("p",null,"You need to write classes that implement these interfaces. Then, you must register those classes with GraphQLite.\nIt you are ",(0,r.yg)("a",{parentName:"p",href:"/docs/4.0/other-frameworks"},"using the ",(0,r.yg)("inlineCode",{parentName:"a"},"SchemaFactory")),", you can register your classes using:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Configure an authentication service (to resolve the @Logged annotations).\n$schemaFactory->setAuthenticationService($myAuthenticationService);\n// Configure an authorization service (to resolve the @Right annotations).\n$schemaFactory->setAuthorizationService($myAuthorizationService);\n")))}p.isMDXComponent=!0}}]);