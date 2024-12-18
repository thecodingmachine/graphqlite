"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[5830],{89654:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>p,contentTitle:()=>l,default:()=>y,frontMatter:()=>r,metadata:()=>o,toc:()=>d});var a=n(58168),i=(n(96540),n(15680));n(67443);const r={id:"extend-input-type",title:"Extending an input type",sidebar_label:"Extending an input type"},l=void 0,o={unversionedId:"extend-input-type",id:"version-8.0.0/extend-input-type",title:"Extending an input type",description:"Available in GraphQLite 4.0+",source:"@site/versioned_docs/version-8.0.0/extend-input-type.mdx",sourceDirName:".",slug:"/extend-input-type",permalink:"/docs/extend-input-type",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-8.0.0/extend-input-type.mdx",tags:[],version:"8.0.0",lastUpdatedBy:"Jacob Thomason",lastUpdatedAt:1734526208,formattedLastUpdatedAt:"Dec 18, 2024",frontMatter:{id:"extend-input-type",title:"Extending an input type",sidebar_label:"Extending an input type"},sidebar:"docs",previous:{title:"Custom argument resolving",permalink:"/docs/argument-resolving"},next:{title:"Class with multiple output types",permalink:"/docs/multiple-output-types"}},p={},d=[],u={toc:d},s="wrapper";function y(e){let{components:t,...n}=e;return(0,i.yg)(s,(0,a.A)({},u,n,{components:t,mdxType:"MDXLayout"}),(0,i.yg)("small",null,"Available in GraphQLite 4.0+"),(0,i.yg)("div",{class:"alert alert--info"},"If you are not familiar with the ",(0,i.yg)("code",null,"#[Factory]")," tag, ",(0,i.yg)("a",{href:"input-types"},'read first the "input types" guide'),"."),(0,i.yg)("p",null,"Fields exposed in a GraphQL input type do not need to be all part of the factory method."),(0,i.yg)("p",null,"Just like with output type (that can be ",(0,i.yg)("a",{parentName:"p",href:"/docs/extend-type"},"extended using the ",(0,i.yg)("inlineCode",{parentName:"a"},"ExtendType")," attribute"),"), you can extend/modify\nan input type using the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Decorate]")," attribute."),(0,i.yg)("p",null,"Use the ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Decorate]")," attribute to add additional fields to an input type that is already declared by a ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Factory]")," attribute,\nor to modify the returned object."),(0,i.yg)("div",{class:"alert alert--info"},"The ",(0,i.yg)("code",null,"#[Decorate]")," attribute is very useful in scenarios where you cannot touch the ",(0,i.yg)("code",null,"#[Factory]")," method. This can happen if the ",(0,i.yg)("code",null,"#[Factory]")," method is defined in a third-party library or if the ",(0,i.yg)("code",null,"#[Factory]")," method is part of auto-generated code."),(0,i.yg)("p",null,"Let's assume you have a ",(0,i.yg)("inlineCode",{parentName:"p"},"Filter")," class used as an input type. You most certainly have a ",(0,i.yg)("inlineCode",{parentName:"p"},"#[Factory]")," to create the input type."),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyFactory\n{\n    #[Factory]\n    public function createFilter(string $name): Filter\n    {\n        // Let's assume you have a flexible 'Filter' class that can accept any kind of filter\n        $filter = new Filter();\n        $filter->addFilter('name', $name);\n        return $filter;\n    }\n}\n")),(0,i.yg)("p",null,"Assuming you ",(0,i.yg)("strong",{parentName:"p"},"cannot"),' modify the code of this factory, you can still modify the GraphQL input type generated by\nadding a "decorator" around the factory.'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyDecorator\n{\n    #[Decorate(inputTypeName: \"FilterInput\")]\n    public function addTypeFilter(Filter $filter, string $type): Filter\n    {\n        $filter->addFilter('type', $type);\n        return $filter;\n    }\n}\n")),(0,i.yg)("p",null,'In the example above, the "Filter" input type is modified. We add an additional "type" field to the input type.'),(0,i.yg)("p",null,"A few things to notice:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The decorator takes the object generated by the factory as first argument"),(0,i.yg)("li",{parentName:"ul"},"The decorator MUST return an object of the same type (or a sub-type)"),(0,i.yg)("li",{parentName:"ul"},"The decorator CAN contain additional parameters. They will be added to the fields of the GraphQL input type."),(0,i.yg)("li",{parentName:"ul"},"The ",(0,i.yg)("inlineCode",{parentName:"li"},"#[Decorate]")," attribute must contain a ",(0,i.yg)("inlineCode",{parentName:"li"},"inputTypeName")," attribute that contains the name of the GraphQL input type\nthat is decorated. If you did not specify this name in the ",(0,i.yg)("inlineCode",{parentName:"li"},"#[Factory]"),' attribute, this is by default the name of the\nPHP class + "Input" (for instance: "Filter" => "FilterInput")')),(0,i.yg)("div",{class:"alert alert--warning"},(0,i.yg)("strong",null,"Heads up!")," The ",(0,i.yg)("code",null,"MyDecorator")," class must exist in the container of your application and the container identifier MUST be the fully qualified class name.",(0,i.yg)("br",null),(0,i.yg)("br",null),"If you are using the Symfony bundle (or a framework with autowiring like Laravel), this is usually not an issue as the container will automatically create the controller entry if you do not explicitly declare it."))}y.isMDXComponent=!0}}]);