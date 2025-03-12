"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[2400],{8239:(t,e,n)=>{n.r(e),n.d(e,{assets:()=>p,contentTitle:()=>r,default:()=>y,frontMatter:()=>o,metadata:()=>l,toc:()=>u});var a=n(58168),i=(n(96540),n(15680));n(67443);const o={id:"input-types",title:"Input types",sidebar_label:"Input types",original_id:"input-types"},r=void 0,l={unversionedId:"input-types",id:"version-3.0/input-types",title:"Input types",description:"Let's admit you are developing an API that returns a list of cities around a location.",source:"@site/versioned_docs/version-3.0/input-types.mdx",sourceDirName:".",slug:"/input-types",permalink:"/docs/3.0/input-types",draft:!1,editUrl:"https://github.com/thecodingmachine/graphqlite/edit/master/website/versioned_docs/version-3.0/input-types.mdx",tags:[],version:"3.0",lastUpdatedBy:"Jacob Thomason",lastUpdatedAt:1741747257,formattedLastUpdatedAt:"Mar 12, 2025",frontMatter:{id:"input-types",title:"Input types",sidebar_label:"Input types",original_id:"input-types"},sidebar:"version-3.0/docs",previous:{title:"External type declaration",permalink:"/docs/3.0/external_type_declaration"},next:{title:"Inheritance and interfaces",permalink:"/docs/3.0/inheritance"}},p={},u=[{value:"Specifying the input type name",id:"specifying-the-input-type-name",level:3}],s={toc:u},c="wrapper";function y(t){let{components:e,...n}=t;return(0,i.yg)(c,(0,a.A)({},s,n,{components:e,mdxType:"MDXLayout"}),(0,i.yg)("p",null,"Let's admit you are developing an API that returns a list of cities around a location."),(0,i.yg)("p",null,"Your GraphQL query might look like this:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre",className:"language-php"},"class MyController\n{\n    /**\n     * @Query\n     * @return City[]\n     */\n    public function getCities(Location $location, float $radius): array\n    {\n        // Some code that returns an array of cities.\n    }\n}\n\n// Class Location is a simple value-object.\nclass Location\n{\n    private $latitude;\n    private $longitude;\n\n    public function __construct(float $latitude, float $longitude)\n    {\n        $this->latitude = $latitude;\n        $this->longitude = $longitude;\n    }\n\n    public function getLatitude(): float\n    {\n        return $this->latitude;\n    }\n\n    public function getLongitude(): float\n    {\n        return $this->longitude;\n    }\n}\n")),(0,i.yg)("p",null,"If you try to run this code, you will get the following error:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},'CannotMapTypeException: cannot map class "Location" to a known GraphQL input type. Check your TypeMapper configuration.\n')),(0,i.yg)("p",null,"You are running into this error because GraphQLite does not know how to handle the ",(0,i.yg)("inlineCode",{parentName:"p"},"Location")," object."),(0,i.yg)("p",null,"In GraphQL, an object passed in parameter of a query or mutation (or any field) is called an ",(0,i.yg)("strong",{parentName:"p"},"Input Type"),"."),(0,i.yg)("p",null,"In order to declare that type, in GraphQLite, we will declare a ",(0,i.yg)("strong",{parentName:"p"},"Factory"),"."),(0,i.yg)("p",null,"A ",(0,i.yg)("strong",{parentName:"p"},"Factory")," is a method that takes in parameter all the fields of the input type and return an object."),(0,i.yg)("p",null,"Here is an example of factory:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},"class MyFactory\n{\n    /**\n     * The Factory annotation will create automatically a LocationInput input type in GraphQL.\n     *\n     * @Factory()\n     */\n    public function createLocation(float $latitude, float $longitude): Location\n    {\n        return new Location($latitude, $longitude);\n    }\n}\n")),(0,i.yg)("p",null,"and now, you can run query like this:"),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},"mutation {\n  getCities(location: {\n              latitude: 45.0,\n              longitude: 0.0,\n            },\n            radius: 42)\n  {\n    id,\n    name\n  }\n}\n")),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"Factories must be declared with the ",(0,i.yg)("strong",{parentName:"li"},"@Factory")," annotation."),(0,i.yg)("li",{parentName:"ul"},"The parameters of the factories are the field of the GraphQL input type")),(0,i.yg)("p",null,"A few important things to notice:"),(0,i.yg)("ul",null,(0,i.yg)("li",{parentName:"ul"},"The container MUST contain the factory class. The identifier of the factory MUST be the fully qualified class name of the class that contains the factory.\nThis is usually already the case if you are using a container with auto-wiring capabilities"),(0,i.yg)("li",{parentName:"ul"},"We recommend that you put the factories in the same directories as the types.")),(0,i.yg)("h3",{id:"specifying-the-input-type-name"},"Specifying the input type name"),(0,i.yg)("p",null,"The GraphQL input type name is derived from the return type of the factory."),(0,i.yg)("p",null,'Given the factory below, the return type is "Location", therefore, the GraphQL input type will be named "LocationInput".'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},"/**\n * @Factory()\n */\npublic function createLocation(float $latitude, float $longitude): Location\n{\n    return new Location($latitude, $longitude);\n}\n")),(0,i.yg)("p",null,'In case you want to override the input type name, you can use the "name" attribute of the @Factory annotation:'),(0,i.yg)("pre",null,(0,i.yg)("code",{parentName:"pre"},'/**\n * @Factory(name="MyNewInputName")\n */\n')),(0,i.yg)("p",null,"Most of the time, the input type name will be completely transparent to you, so there is no real reason\nto customize it."))}y.isMDXComponent=!0}}]);