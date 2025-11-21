import React from 'react';
import Layout from '@theme/Layout';

const showcaseItems = [
    {
        title: 'BrickNPC Admin Dashboard',
        description: 'The admin dashboard used for all apps in the BrickNPC ecosystem.',
        image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
        link: 'https://github.com/bricknpc/dashboard'
    },
    // Add your project here
];

function ShowcaseCard({ title, description, image, link }) {
    return (
        <div style={{
            backgroundColor: 'var(--ifm-card-background-color)',
            borderRadius: '8px',
            overflow: 'hidden',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
            transition: 'transform 0.2s, box-shadow 0.2s',
            height: '100%',
            display: 'flex',
            flexDirection: 'column'
        }}
             onMouseEnter={(e) => {
                 e.currentTarget.style.transform = 'translateY(-4px)';
                 e.currentTarget.style.boxShadow = '0 4px 16px rgba(0,0,0,0.15)';
             }}
             onMouseLeave={(e) => {
                 e.currentTarget.style.transform = 'translateY(0)';
                 e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
             }}>
            <div style={{
                width: '100%',
                height: '200px',
                overflow: 'hidden',
                backgroundColor: '#f0f0f0'
            }}>
                <img
                    src={image}
                    alt={title}
                    style={{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover'
                    }}
                />
            </div>
            <div style={{
                padding: '1.5rem',
                flex: 1,
                display: 'flex',
                flexDirection: 'column'
            }}>
                <h3 style={{
                    marginTop: 0,
                    marginBottom: '0.75rem',
                    fontSize: '1.25rem'
                }}>
                    {title}
                </h3>
                <p style={{
                    color: 'var(--ifm-color-emphasis-700)',
                    marginBottom: '1.5rem',
                    flex: 1
                }}>
                    {description}
                </p>
                <a
                    href={link}
                    target="_blank"
                    rel="noopener noreferrer"
                    style={{
                        display: 'inline-block',
                        padding: '0.5rem 1.5rem',
                        backgroundColor: 'var(--ifm-color-primary)',
                        color: 'white',
                        borderRadius: '6px',
                        textDecoration: 'none',
                        textAlign: 'center',
                        fontWeight: '500',
                        transition: 'background-color 0.2s'
                    }}
                    onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = 'var(--ifm-color-primary-dark)';
                    }}
                    onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = 'var(--ifm-color-primary)';
                    }}>
                    Visit Site →
                </a>
            </div>
        </div>
    );
}

export default function Showcase() {
    return (
        <Layout title="Community Showcase" description="A showcase of projects using Eloquent Tables">
            <div style={{
                maxWidth: '1200px',
                minWidth: '60%',
                margin: '0 auto',
                padding: '3rem 2rem'
            }}>
                <header style={{
                    textAlign: 'center',
                    marginBottom: '3rem'
                }}>
                    <h1 style={{
                        fontSize: '2.5rem',
                        marginBottom: '1rem'
                    }}>
                        Community Showcase
                    </h1>
                    <p style={{
                        fontSize: '1.125rem',
                        color: 'var(--ifm-color-emphasis-700)',
                        maxWidth: '600px',
                        margin: '0 auto'
                    }}>
                        Discover amazing projects built with Eloquent Tables by our community
                    </p>
                </header>

                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))',
                    gap: '2rem'
                }}>
                    {showcaseItems.map((item, index) => (
                        <ShowcaseCard key={index} {...item} />
                    ))}
                </div>
            </div>
        </Layout>
    );
}