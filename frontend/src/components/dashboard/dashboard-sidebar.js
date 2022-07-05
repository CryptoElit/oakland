import {useEffect, useMemo} from 'react';
import NextLink from 'next/link';
import {useRouter} from 'next/router';
import PropTypes from 'prop-types';
import {useTranslation} from 'react-i18next';
import {Box, Divider, Drawer, useMediaQuery} from '@mui/material';
import {ReportsIcon as ReportIcon} from '../../icons/reports-icon';
import {DashboardIcon as DashCon} from '../../icons/dashboard-icon';
import {DefendantsIcon as DefendantIcon} from '../../icons/defendants-icon';
import {CitationIcon as CitationIcon} from '../../icons/citation-icon';
import {UsersIcon as UserIcon} from '../../icons/users-icon';
import {DocketsIcon as DocketsIcon} from '../../icons/dockets-icon';
import {Cog as CogIcon} from '../../icons/cog';
import {Scrollbar} from '../scrollbar';
import {DashboardSidebarSection} from './dashboard-sidebar-section';

const getSections = (t) => [
    {
        title: false,
        items: [
            {
                title: t('Dashboard'),
                path: '/dashboard',
                icon: <DashCon fontSize="small"/>
            },
            {
                title: t('Citations'),
                path: '/citations',
                icon: <CitationIcon fontSize="small"/>
            },
            {
                title: t('Defendants'),
                path: '/defendants',
                icon: <DefendantIcon fontSize="small"/>
            },
            {
                title: t('Dockets'),
                path: '/dockets',
                icon: <DocketsIcon fontSize="small"/>
            },
            {
                title: t('Reports'),
                path: '/reports',
                icon: <ReportIcon fontSize="small"/>
            },
            {
                title: t('Users'),
                path: '/users',
                icon: <UserIcon fontSize="small"/>
            },
            {
                title: t('Settings'),
                path: 'settings',
                icon: <CogIcon fontSize="small"/>
            },
        ]
    }
];


export const DashboardSidebar = (props) => {
    const {onClose, open} = props;
    const router = useRouter();
    const {t} = useTranslation();
    const lgUp = useMediaQuery((theme) => theme.breakpoints.up('lg'), {
        noSsr: true
    });
    const sections = useMemo(() => getSections(t), [t]);


    const handlePathChange = () => {
        if (!router.isReady) {
            return;
        }

        if (open) {
            onClose?.();
        }
    };

    useEffect(handlePathChange,
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [router.isReady, router.asPath]);


    const content = (
        <>
            <Scrollbar
                sx={{
                    height: '100%',
                    '& .simplebar-content': {

                        height: '100%',
                        backgroundColor: 'white'
                    }
                }}
            >
                <Box
                    sx={{
                        display: 'flex',
                        flexDirection: 'column',
                        height: '100%'
                    }}
                >
                    <div>
                        <Box sx={{pt: 2, pl: 2}}>
                            <NextLink
                                href="/"
                                passHref
                            >
                                <a>
                                    <img
                                        alt=""
                                        src="/static/logo-admin-portal.png"
                                    />
                                </a>
                            </NextLink>
                        </Box>
                    </div>

                    <Box sx={{flexGrow: 1}}>
                        {sections.map((section) => (
                            <DashboardSidebarSection
                                key={section.title}
                                path={router.asPath}
                                sx={{
                                    mt: 1,
                                    '& + &': {
                                        mt: 2
                                    }
                                }}
                                {...section} />
                        ))}
                    </Box>
                    <Divider
                        sx={{
                            borderColor: '#2D3748'  // dark divider
                        }}
                    />
                </Box>
            </Scrollbar>

        </>
    );

    if (lgUp) {
        return (
            <Drawer
                anchor="left"
                open
                PaperProps={{
                    sx: {
                        backgroundColor: 'neutral.900',
                        borderRightColor: 'divider',
                        borderRightStyle: 'solid',
                        borderRightWidth: (theme) => theme.palette.mode === 'dark' ? 1 : 0,
                        color: '#FFFFFF',
                        width: 280
                    }
                }}
                variant="permanent"
            >
                {content}
            </Drawer>
        );
    }

    return (
        <Drawer
            anchor="left"
            onClose={onClose}
            open={open}
            PaperProps={{
                sx: {
                    backgroundColor: 'neutral.900',
                    color: '#FFFFFF',
                    width: 280
                }
            }}
            sx={{zIndex: (theme) => theme.zIndex.appBar + 100}}
            variant="temporary"
        >
            {content}
        </Drawer>
    );
};

DashboardSidebar.propTypes = {
    onClose: PropTypes.func,
    open: PropTypes.bool
};
