"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[8112],{17678:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>u,contentTitle:()=>o,default:()=>p,frontMatter:()=>r,metadata:()=>s,toc:()=>l});var a=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"fine-grained-security",title:"Fine grained security",sidebar_label:"Fine grained security"},o=void 0,s={unversionedId:"fine-grained-security",id:"fine-grained-security",title:"Fine grained security",description:"If the #[Logged] and #[Right] attributes are not",source:"@site/docs/fine-grained-security.mdx",sourceDirName:".",slug:"/fine-grained-security",permalink:"/docs/next/fine-grained-security",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/docs/fine-grained-security.mdx",tags:[],version:"current",lastUpdatedBy:"Andrii Dembitskyi",lastUpdatedAt:1732077135,formattedLastUpdatedAt:"Nov 20, 2024",frontMatter:{id:"fine-grained-security",title:"Fine grained security",sidebar_label:"Fine grained security"},sidebar:"docs",previous:{title:"Authentication and authorization",permalink:"/docs/next/authentication-authorization"},next:{title:"Connecting security to your framework",permalink:"/docs/next/implementing-security"}},u={},l=[{value:"Using the #Security attribute",id:"using-the-security-attribute",level:2},{value:"The <code>is_granted</code> function",id:"the-is_granted-function",level:2},{value:"Accessing method parameters",id:"accessing-method-parameters",level:2},{value:"Setting HTTP code and error message",id:"setting-http-code-and-error-message",level:2},{value:"Setting a default value",id:"setting-a-default-value",level:2},{value:"Accessing the user",id:"accessing-the-user",level:2},{value:"Accessing the current object",id:"accessing-the-current-object",level:2},{value:"Available scope",id:"available-scope",level:2},{value:"How to restrict access to a given resource",id:"how-to-restrict-access-to-a-given-resource",level:2}],c={toc:l},g="wrapper";function p(e){let{components:t,...n}=e;return(0,i.yg)(g,(0,a.A)({},c,n,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"If the ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/authentication-authorization#logged-and-right-annotations"},(0,i.yg)("inlineCode",{parentName:"a"},"#[Logged]")," and ",(0,i.yg)("inlineCode",{parentName:"a"},"#[Right]")," attributes")," are not\ngranular enough for your needs, you can use the advanced ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute."),(0,i.yg)("p",null,"Using the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute, you can write an ",(0,i.yg)("em",{parentName:"p"},"expression")," that can contain custom logic. For instance:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"Check that a user can access a given resource"),(0,i.yg)("li",{parentName:"ul"},"Check that a user has one right or another right"),(0,i.yg)("li",{parentName:"ul"},"...")),(0,i.yg)("h2",{id:"using-the-security-attribute"},"Using the #","[Security]"," attribute"),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute is very flexible: it allows you to pass an expression that can contains custom logic:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"use TheCodingMachine\\GraphQLite\\Annotations\\Security;\n\n// ...\n\n#[Query]\n#[Security(\"is_granted('ROLE_ADMIN') or is_granted('POST_SHOW', post)\")]\npublic function getPost(Post $post): array\n{\n    // ...\n}\n")),(0,i.yg)("p",null,"The ",(0,i.yg)("em",{parentName:"p"},"expression")," defined in the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute must conform to ",(0,i.yg)("a",{parentName:"p",href:"https://symfony.com/doc/4.4/components/expression_language/syntax.html"},"Symfony's Expression Language syntax")),(0,i.yg)("div",{class:"alert alert--info"},"If you are a Symfony user, you might already be used to the ",(0,i.yg)("code",null,"#[Security]")," attribute. Most of the inspiration of this attribute comes from Symfony. Warning though! GraphQLite's ",(0,i.yg)("code",null,"#[Security]")," attribute and Symfony's ",(0,i.yg)("code",null,"#[Security]")," attribute are slightly different. Especially, the two attributes do not live in the same namespace!"),(0,i.yg)("h2",{id:"the-is_granted-function"},"The ",(0,i.yg)("inlineCode",{parentName:"h2"},"is_granted")," function"),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"is_granted")," function to check if a user has a special right."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Security(\"is_granted('ROLE_ADMIN')\")]\n")),(0,i.yg)("p",null,"is similar to"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'#[Right("ROLE_ADMIN")]\n')),(0,i.yg)("p",null,"In addition, the ",(0,i.yg)("inlineCode",{parentName:"p"},"is_granted"),' function accepts a second optional parameter: the "scope" of the right.'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Query]\n#[Security(\"is_granted('POST_SHOW', post)\")]\npublic function getPost(Post $post): array\n{\n    // ...\n}\n")),(0,i.yg)("p",null,"In the example above, the ",(0,i.yg)("inlineCode",{parentName:"p"},"getPost")," method can be called only if the logged user has the 'POST_SHOW' permission on the\n",(0,i.yg)("inlineCode",{parentName:"p"},"$post")," object. You can notice that the ",(0,i.yg)("inlineCode",{parentName:"p"},"$post")," object comes from the parameters."),(0,i.yg)("h2",{id:"accessing-method-parameters"},"Accessing method parameters"),(0,i.yg)("p",null,"All parameters passed to the method can be accessed in the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," expression."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'#[Query]\n#[Security(expression: "startDate < endDate", statusCode: 400, message: "End date must be after start date")]\npublic function getPosts(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array\n{\n    // ...\n}\n')),(0,i.yg)("p",null,"In the example above, we tweak a bit the Security attribute purpose to do simple input validation."),(0,i.yg)("h2",{id:"setting-http-code-and-error-message"},"Setting HTTP code and error message"),(0,i.yg)("p",null,"You can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"statusCode")," and ",(0,i.yg)("inlineCode",{parentName:"p"},"message")," attributes to set the HTTP code and GraphQL error message."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'#[Query]\n#[Security(expression: "is_granted(\'POST_SHOW\', post)", statusCode: 404, message: "Post not found (let\'s pretend the post does not exists!)")]\npublic function getPost(Post $post): array\n{\n    // ...\n}\n')),(0,i.yg)("p",null,"Note: since a single GraphQL call contain many errors, 2 errors might have conflicting HTTP status code.\nThe resulting status code is up to the GraphQL middleware you use. Most of the time, the status code with the\nhigher error code will be returned."),(0,i.yg)("h2",{id:"setting-a-default-value"},"Setting a default value"),(0,i.yg)("p",null,"If you do not want an error to be thrown when the security condition is not met, you can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"failWith")," attribute\nto set a default value."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Query]\n#[Security(expression: \"is_granted('CAN_SEE_MARGIN', this)\", failWith: null)]\npublic function getMargin(): float\n{\n    // ...\n}\n")),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"failWith")," attribute behaves just like the ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/authentication-authorization#not-throwing-errors"},(0,i.yg)("inlineCode",{parentName:"a"},"#[FailWith]")," attribute"),"\nbut for a given ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute."),(0,i.yg)("p",null,"You cannot use the ",(0,i.yg)("inlineCode",{parentName:"p"},"failWith")," attribute along ",(0,i.yg)("inlineCode",{parentName:"p"},"statusCode")," or ",(0,i.yg)("inlineCode",{parentName:"p"},"message")," attributes."),(0,i.yg)("h2",{id:"accessing-the-user"},"Accessing the user"),(0,i.yg)("p",null,"You can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"user")," variable to access the currently logged user.\nYou can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"is_logged()")," function to check if a user is logged or not."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'#[Query]\n#[Security("is_logged() && user.age > 18")]\npublic function getNSFWImages(): array\n{\n    // ...\n}\n')),(0,i.yg)("h2",{id:"accessing-the-current-object"},"Accessing the current object"),(0,i.yg)("p",null,"You can use the ",(0,i.yg)("inlineCode",{parentName:"p"},"this")," variable to access any (public) property / method of the current class."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},'class Post {\n    #[Field]\n    #[Security("this.canAccessBody(user)")]\n    public function getBody(): array\n    {\n        // ...\n    }\n\n    public function canAccessBody(User $user): bool\n    {\n        // Some custom logic here\n    }\n}\n')),(0,i.yg)("h2",{id:"available-scope"},"Available scope"),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Security]")," attribute can be used in any query, mutation or field, so anywhere you have a ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Query]"),", ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Mutation]"),"\nor ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Field]")," attribute."),(0,i.yg)("h2",{id:"how-to-restrict-access-to-a-given-resource"},"How to restrict access to a given resource"),(0,i.yg)("p",null,"The ",(0,i.yg)("inlineCode",{parentName:"p"},"is_granted")," method can be used to restrict access to a specific resource."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"#[Security(\"is_granted('POST_SHOW', post)\")]\n")),(0,i.yg)("p",null,"If you are wondering how to configure these fine-grained permissions, this is not something that GraphQLite handles\nitself. Instead, this depends on the framework you are using."),(0,i.yg)("p",null,"If you are using Symfony, you will ",(0,i.yg)("a",{parentName:"p",href:"https://symfony.com/doc/current/security/voters.html"},"create a custom voter"),"."),(0,i.yg)("p",null,"If you are using Laravel, you will ",(0,i.yg)("a",{parentName:"p",href:"https://laravel.com/docs/6.x/authorization"},"create a Gate or a Policy"),"."),(0,i.yg)("p",null,"If you are using another framework, you need to know that the ",(0,i.yg)("inlineCode",{parentName:"p"},"is_granted")," function simply forwards the call to\nthe ",(0,i.yg)("inlineCode",{parentName:"p"},"isAllowed")," method of the configured ",(0,i.yg)("inlineCode",{parentName:"p"},"AuthorizationSerice"),". See ",(0,i.yg)("a",{parentName:"p",href:"/docs/next/implementing-security"},"Connecting GraphQLite to your framework's security module\n")," for more details"))}p.isMDXComponent=!0}}]);