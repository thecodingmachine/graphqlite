/**
 * Copyright (c) 2017-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

const React = require('react');

const CompLibrary = require('../../core/CompLibrary.js');

const MarkdownBlock = CompLibrary.MarkdownBlock; /* Used to read markdown */
const Container = CompLibrary.Container;
const GridBlock = CompLibrary.GridBlock;

class HomeSplash extends React.Component {
  render() {
    const {siteConfig, language = ''} = this.props;
    const {baseUrl, docsUrl} = siteConfig;
    const docsPart = `${docsUrl ? `${docsUrl}/` : ''}`;
    const langPart = `${language ? `${language}/` : ''}`;
    const docUrl = doc => `${baseUrl}${docsPart}${langPart}${doc}`;

    const SplashContainer = props => (
      <div className="homeContainer">
        <div className="homeSplashFade">
          <div className="wrapper homeWrapper">{props.children}</div>
        </div>
      </div>
    );

    /*const Logo = props => (
      <div className="projectLogo">
        <img src={props.img_src} alt="Project Logo" />
      </div>
    );*/

    const ProjectTitle = () => (
      <h2 className="projectTitle">
        {siteConfig.title}
        <small>{siteConfig.tagline}</small>
      </h2>
    );

    const PromoSection = props => (
      <div className="section promoSection">
        <div className="promoRow">
          <div className="pluginRowBlock">{props.children}</div>
        </div>
      </div>
    );

    const Button = props => (
      <div className="pluginWrapper buttonWrapper">
        <a className="button" href={props.href} target={props.target}>
          {props.children}
        </a>
      </div>
    );

    return (
      <SplashContainer>
        <div className="inner">
          <ProjectTitle siteConfig={siteConfig} />
            <img width={200} src="img/logo.svg" alt="Project Logo" />
          <PromoSection>
            <Button href="#try">Try It Out</Button>
            <Button href={docUrl('getting-started')}>Install</Button>
            <Button href="https://github.com/thecodingmachine/graphqlite">GitHub</Button>
          </PromoSection>
        </div>
      </SplashContainer>
    );
  }
}

class Index extends React.Component {
  render() {
    const {config: siteConfig, language = ''} = this.props;
    const {baseUrl} = siteConfig;

    const Block = props => (
      <Container
        padding={['bottom', 'top']}
        id={props.id}
        background={props.background}>
        <GridBlock
          align="center"
          contents={props.children}
          layout={props.layout}
        />
      </Container>
    );

    const FeatureCallout = () => (
      <div
        className="productShowcaseSection paddingBottom"
        >
        <h2 style={{textAlign: 'center'}}>Comes with batteries included</h2>
          <ul>
              <li><a href="docs/my-first-query">Queries</a></li>
              <li><a href="docs/mutations">Mutations</a></li>
              <li><a href="docs/type_mapping#mapping-of-arrays">Mapping of arrays / iterators</a></li>
              <li><a href="docs/input-types">Support for input types</a></li>
              <li><a href="docs/extend_type">Extendable types</a></li>
              <li>Plugs into your <a href="docs/authentication_authorization">framework security module</a></li>
              <li>Map <a href="docs/inheritance">PHP inheritance to GraphQL interfaces</a> automatically</li>
              <li><a href="docs/type_mapping#union-types">Union types</a></li>
              <li><a href="docs/file-uploads">GraphQL file uploads</a></li>
              <li><a href="docs/type_mapping#mapping-of-dates">Native DateTime type</a></li>
          </ul>
      </div>
    );

    /*const LearnHow = () => (
      <Block background="light">
        {[
          {
            content: 'Talk about learning how to use this. <pre><code>Foo</code></pre>',
            image: `${baseUrl}img/docusaurus.svg`,
            imageAlign: 'right',
            title: 'Create a complete GraphQL API using simple annotations',
          },
        ]}
      </Block>
    );*/

      const LearnHow = () => (
          <Container background="light">
              <GridBlock
                  layout="twoColumn"
                  contents={[
                      {
                          title: `Use PHP Annotations to declare your GraphQL API`,
                          content: `Create a complete GraphQL API by simply **annotating** your PHP classes.

You focus on your PHP code.

GraphQLite comes with an advanced *type-mapper* that automatically translate your PHP types into GraphQL types.`,
                      },
                      {
                          content: '```php\n' +
                              '/**\n' +
                              ' * @Type()\n' +
                              ' */\n' +
                              'class Product\n' +
                              '{\n' +
                              '    /**\n' +
                              '     * @Field()\n' +
                              '     */\n' +
                              '    public function getName(): string\n' +
                              '    {\n' +
                              '        return $this->name;\n' +
                              '    }\n',
                      },
                  ]}
              />
          </Container>
      );

    const Features = () => (
      <Block layout="fourColumn">
        {[
          {
            content: 'Almost [no code needed](docs/my-first-query) to switch your API to GraphQL!',
            image: `${baseUrl}img/at2.svg`,
            imageLink: "docs/my-first-query",
            imageAlign: 'top',
            title: 'Annotation based',
          },
          {
            content: 'Can be [installed in any PHP project](docs/other-frameworks)',
            image: `${baseUrl}img/php-fig.jpg`,
            imageLink: "docs/other-frameworks",
            imageAlign: 'top',
            title: 'Framework agnostic',
          },
      {
          content: 'Native [integration with Symfony provided](docs/symfony-bundle)',
          image: `${baseUrl}img/symfony_black_03.svg`,
          imageLink: "docs/symfony-bundle",
          imageAlign: 'top',
          title: '... but Symfony bundle available',
      },
        ]}
      </Block>
    );

    const Showcase = () => {
      if ((siteConfig.users || []).length === 0) {
        return null;
      }

      const showcase = siteConfig.users
        .filter(user => user.pinned)
        .map(user => (
          <a href={user.infoLink} key={user.infoLink}>
            <img src={user.image} alt={user.caption} title={user.caption} />
          </a>
        ));

      const pageUrl = page => baseUrl + (language ? `${language}/` : '') + page;

      return (
        <div className="productShowcaseSection paddingBottom">
          <h2>Who is Using This?</h2>
          <p>This project is used by all these people</p>
          <div className="logos">{showcase}</div>
          <div className="more-users">
            <a className="button" href={pageUrl('users.html')}>
              More {siteConfig.title} Users
            </a>
          </div>
        </div>
      );
    };


      const Sample1 = () => (
          <Container background="light">
              <GridBlock
                  layout="twoColumn"
                  contents={[
                      {
                          title: `1. Declare a query in your controller`,
                          content: `Annotate the query with the @Query annotation`,
                      },
                      {
                          content: `
\`\`\`php
class ProductController
{
    /**
     * @Query
     */
    public function product(string $id): Product
    {
        // Some code that looks for a product and returns it.
    }
}
\`\`\`
` ,
                      },
                  ]}
              />
          </Container>
      );

      const Sample2 = () => (
          <Container background="">
              <GridBlock
                  layout="twoColumn"
                  contents={[
                      {
                          content: `
\`\`\`php
/**
 * @Type()
 */
class Product
{
    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
    }
    // ...
}
\`\`\`
` ,
                      },
                      {
                          title: `2. Annotate your types`,
                          content: "Annotate the `Product` class to declare what fields are exposed to the GraphQL API",
                      },
                  ]}
              />
          </Container>
      );

      const Sample3 = () => (
          <Container background="light">
              <GridBlock
                  layout="twoColumn"
                  contents={[
                      {
                          title: `3. Query and enjoy`,
                          content: `You're good to go! Query and enjoy!`,
                      },
                      {
                          content: `
\`\`\`graphql
{
  product(id: 42) {
    name
  }
}
\`\`\`
` ,
                      },
                  ]}
              />
          </Container>
      );

    return (
      <div>
        <HomeSplash siteConfig={siteConfig} language={language} />
        <div className="mainContainer">
          <LearnHow />
          <Features />
          <FeatureCallout />
            <Container background="dark">
                <h2 style={{textAlign: 'center'}}>Get started!</h2>
            </Container>
          <Sample1 />
          <Sample2 />
          <Sample3 />
            <Showcase/>
        </div>
      </div>
    );
  }
}

module.exports = Index;
