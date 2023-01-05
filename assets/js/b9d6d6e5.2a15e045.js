"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2808],{2386:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>s,contentTitle:()=>r,default:()=>p,frontMatter:()=>o,metadata:()=>l,toc:()=>d});var n=a(7462),i=(a(7294),a(3905));a(1839);const o={id:"migrating",title:"Migrating",sidebar_label:"Migrating",original_id:"migrating"},r=void 0,l={unversionedId:"migrating",id:"version-4.0/migrating",title:"Migrating",description:"Migrating from v3.0 to v4.0",source:"@site/versioned_docs/version-4.0/migrating.md",sourceDirName:".",slug:"/migrating",permalink:"/docs/4.0/migrating",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-4.0/migrating.md",tags:[],version:"4.0",lastUpdatedBy:"Snyk bot",lastUpdatedAt:1672898619,formattedLastUpdatedAt:"1/5/2023",frontMatter:{id:"migrating",title:"Migrating",sidebar_label:"Migrating",original_id:"migrating"},sidebar:"version-4.0/docs",previous:{title:"Troubleshooting",permalink:"/docs/4.0/troubleshooting"},next:{title:"Annotations reference",permalink:"/docs/4.0/annotations_reference"}},s={},d=[{value:"Migrating from v3.0 to v4.0",id:"migrating-from-v30-to-v40",level:2}],u={toc:d};function p(e){let{components:t,...a}=e;return(0,i.kt)("wrapper",(0,n.Z)({},u,a,{components:t,mdxType:"MDXLayout"}),(0,i.kt)("h2",{id:"migrating-from-v30-to-v40"},"Migrating from v3.0 to v4.0"),(0,i.kt)("p",null,'If you are a "regular" GraphQLite user, migration to v4 should be straightforward:'),(0,i.kt)("ul",null,(0,i.kt)("li",{parentName:"ul"},"Annotations are mostly untouched. The only annotation that is changed is the ",(0,i.kt)("inlineCode",{parentName:"li"},"@SourceField")," annotation.",(0,i.kt)("ul",{parentName:"li"},(0,i.kt)("li",{parentName:"ul"},"Check your code for every places where you use the ",(0,i.kt)("inlineCode",{parentName:"li"},"@SourceField")," annotation:"),(0,i.kt)("li",{parentName:"ul"},'The "id" attribute has been remove (',(0,i.kt)("inlineCode",{parentName:"li"},"@SourceField(id=true)"),"). Instead, use ",(0,i.kt)("inlineCode",{parentName:"li"},'@SourceField(outputType="ID")')),(0,i.kt)("li",{parentName:"ul"},'The "logged", "right" and "failWith" attributes have been removed (',(0,i.kt)("inlineCode",{parentName:"li"},"@SourceField(logged=true)"),").\nInstead, use the annotations attribute with the same annotations you use for the ",(0,i.kt)("inlineCode",{parentName:"li"},"@Field")," annotation:\n",(0,i.kt)("inlineCode",{parentName:"li"},"@SourceField(annotations={@Logged, @FailWith(null)})")),(0,i.kt)("li",{parentName:"ul"},"If you use magic property and were creating a getter for every magic property (to put a ",(0,i.kt)("inlineCode",{parentName:"li"},"@Field")," annotation on it),\nyou can now replace this getter with a ",(0,i.kt)("inlineCode",{parentName:"li"},"@MagicField")," annotation."))),(0,i.kt)("li",{parentName:"ul"},"In GraphQLite v3, the default was to hide a field from the schema if a user has no access to it.\nIn GraphQLite v4, the default is to still show this field, but to throw an error if the user makes a query on it\n(this way, the schema is the same for all users). If you want the old mode, use the new\n",(0,i.kt)("a",{parentName:"li",href:"/docs/4.0/annotations_reference#hideifunauthorized-annotation"},(0,i.kt)("inlineCode",{parentName:"a"},"@HideIfUnauthorized")," annotation")),(0,i.kt)("li",{parentName:"ul"},"If you are using the Symfony bundle, the Laravel package or the Universal module, you must also upgrade those to 4.0.\nThese package will take care of the wiring for you. Apart for upgrading the packages, you have nothing to do."),(0,i.kt)("li",{parentName:"ul"},"If you are relying on the ",(0,i.kt)("inlineCode",{parentName:"li"},"SchemaFactory")," to bootstrap GraphQLite, you have nothing to do.")),(0,i.kt)("p",null,"On the other hand, if you are a power user and if you are wiring GraphQLite services yourself (without using the\n",(0,i.kt)("inlineCode",{parentName:"p"},"SchemaFactory"),') or if you implemented custom "TypeMappers", you will need to adapt your code:'),(0,i.kt)("ul",null,(0,i.kt)("li",{parentName:"ul"},"The ",(0,i.kt)("inlineCode",{parentName:"li"},"FieldsBuilderFactory")," is gone. Directly instantiate ",(0,i.kt)("inlineCode",{parentName:"li"},"FieldsBuilder")," in v4."),(0,i.kt)("li",{parentName:"ul"},"The ",(0,i.kt)("inlineCode",{parentName:"li"},"CompositeTypeMapper")," class has no more constructor arguments. Use the ",(0,i.kt)("inlineCode",{parentName:"li"},"addTypeMapper")," method to register\ntype mappers in it."),(0,i.kt)("li",{parentName:"ul"},"The ",(0,i.kt)("inlineCode",{parentName:"li"},"FieldsBuilder")," now accept an extra argument: the ",(0,i.kt)("inlineCode",{parentName:"li"},"RootTypeMapper")," that you need to instantiate accordingly. Take\na look at the ",(0,i.kt)("inlineCode",{parentName:"li"},"SchemaFactory")," class for an example of proper configuration."),(0,i.kt)("li",{parentName:"ul"},"The ",(0,i.kt)("inlineCode",{parentName:"li"},"HydratorInterface")," and all implementations are gone. When returning an input object from a TypeMapper, the object\nmust now implement the ",(0,i.kt)("inlineCode",{parentName:"li"},"ResolvableMutableInputInterface")," (an input object type that contains its own resolver)")),(0,i.kt)("p",null,"Note: we strongly recommend to use the Symfony bundle, the Laravel package, the Universal module or the SchemaManager\nto bootstrap GraphQLite. Wiring directly GraphQLite classes (like the ",(0,i.kt)("inlineCode",{parentName:"p"},"FieldsBuilder"),") into your container is not recommended,\nas the signature of the constructor of those classes may vary from one minor release to another.\nUse the ",(0,i.kt)("inlineCode",{parentName:"p"},"SchemaManager")," instead."))}p.isMDXComponent=!0}}]);