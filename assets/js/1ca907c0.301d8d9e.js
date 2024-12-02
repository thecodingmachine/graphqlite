"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[1431],{85483:(e,t,i)=>{i.r(t),i.d(t,{assets:()=>c,contentTitle:()=>o,default:()=>h,frontMatter:()=>a,metadata:()=>s,toc:()=>l});var n=i(58168),r=(i(96540),i(15680));i(67443);const a={id:"implementing-security",title:"Connecting GraphQLite to your framework's security module",sidebar_label:"Connecting security to your framework"},o=void 0,s={unversionedId:"implementing-security",id:"version-7.0.0/implementing-security",title:"Connecting GraphQLite to your framework's security module",description:"At the time of writing, the Symfony Bundle and the Laravel package handle this implementation. For the latest documentation, please see their respective Github repositories.",source:"@site/versioned_docs/version-7.0.0/implementing-security.md",sourceDirName:".",slug:"/implementing-security",permalink:"/docs/implementing-security",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-7.0.0/implementing-security.md",tags:[],version:"7.0.0",lastUpdatedBy:"dependabot[bot]",lastUpdatedAt:1733174531,formattedLastUpdatedAt:"Dec 2, 2024",frontMatter:{id:"implementing-security",title:"Connecting GraphQLite to your framework's security module",sidebar_label:"Connecting security to your framework"},sidebar:"docs",previous:{title:"Fine grained security",permalink:"/docs/fine-grained-security"},next:{title:"Operation complexity",permalink:"/docs/operation-complexity"}},c={},l=[],u={toc:l},p="wrapper";function h(e){let{components:t,...i}=e;return(0,r.yg)(p,(0,n.A)({},u,i,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("div",{class:"alert alert--info"},"At the time of writing, the Symfony Bundle and the Laravel package handle this implementation. For the latest documentation, please see their respective Github repositories."),(0,r.yg)("p",null,"GraphQLite needs to know if a user is logged or not, and what rights it has.\nBut this is specific of the framework you use."),(0,r.yg)("p",null,"To plug GraphQLite to your framework's security mechanism, you will have to provide two classes implementing:"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthenticationServiceInterface")),(0,r.yg)("li",{parentName:"ul"},(0,r.yg)("inlineCode",{parentName:"li"},"TheCodingMachine\\GraphQLite\\Security\\AuthorizationServiceInterface"))),(0,r.yg)("p",null,"Those two interfaces act as adapters between GraphQLite and your framework:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthenticationServiceInterface\n{\n    /**\n     * Returns true if the "current" user is logged\n     */\n    public function isLogged(): bool;\n\n    /**\n     * Returns an object representing the current logged user.\n     * Can return null if the user is not logged.\n     */\n    public function getUser(): ?object;\n}\n')),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},'interface AuthorizationServiceInterface\n{\n    /**\n     * Returns true if the "current" user has access to the right "$right"\n     *\n     * @param mixed $subject The scope this right applies on. $subject is typically an object or a FQCN. Set $subject to "null" if the right is global.\n     */\n    public function isAllowed(string $right, $subject = null): bool;\n}\n')),(0,r.yg)("p",null,"You need to write classes that implement these interfaces. Then, you must register those classes with GraphQLite.\nIt you are ",(0,r.yg)("a",{parentName:"p",href:"/docs/other-frameworks"},"using the ",(0,r.yg)("inlineCode",{parentName:"a"},"SchemaFactory")),", you can register your classes using:"),(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Configure an authentication service (to resolve the @Logged annotations).\n$schemaFactory->setAuthenticationService($myAuthenticationService);\n// Configure an authorization service (to resolve the @Right annotations).\n$schemaFactory->setAuthorizationService($myAuthorizationService);\n")))}h.isMDXComponent=!0}}]);