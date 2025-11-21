import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Easy to Use',
    Svg: require('@site/static/img/undraw_product-iteration.svg').default,
    description: (
      <>
          Build clean, accessible, and fully functional HTML tables without touching markup.
      </>
    ),
  },
  {
    title: 'Configured by Code',
    Svg: require('@site/static/img/undraw_code-review.svg').default,
    description: (
      <>
          Define your tables entirely in PHP. No HTML, CSS, JavaScript, or accessibility expertise required. Just write eloquent and fluent Laravel code.
      </>
    ),
  },
  {
    title: 'Choose your frontend',
    Svg: require('@site/static/img/undraw_ideas-flow.svg').default,
    description: (
      <>
          Style your tables with built-in Bootstrap 5 support or publish the view files to craft your own custom theme.
      </>
    ),
  },
  {
    title: 'Accessible by Design',
    Svg: require('@site/static/img/undraw_web-browsing.svg').default,
    description: (
      <>
          Outputs fully validated and accessible HTML5 tables that follow best practices out of the box.
      </>
    ),
  },
  {
    title: 'Powerful Features',
    Svg: require('@site/static/img/undraw_abstract.svg').default,
    description: (
      <>
        Add pagination, sorting, searching, filtering, actions, and more - all defined in PHP with no need for front-end wiring.
       </>
    ),
  },
  {
    title: 'Built for Laravel Developers',
    Svg: require('@site/static/img/undraw_laravel-and-vue.svg').default,
    description: (
      <>
        Seamlessly integrates with Eloquent, authorization, policies, resources, and route models for a natural Laravel workflow.
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
