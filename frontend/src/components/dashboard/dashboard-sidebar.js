import { useEffect, useMemo, useRef, useState } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import PropTypes from 'prop-types';
import { useTranslation } from 'react-i18next';
import { Box, Button, Chip, Divider, Drawer, Typography, useMediaQuery } from '@mui/material';
import { Calendar as CalendarIcon } from '../../icons/calendar';
import { Cash as CashIcon } from '../../icons/cash';
import { ChartBar as ChartBarIcon } from '../../icons/chart-bar';
import { ChartPie as ChartPieIcon } from '../../icons/chart-pie';
import { ChatAlt2 as ChatAlt2Icon } from '../../icons/chat-alt2';
import { ClipboardList as ClipboardListIcon } from '../../icons/clipboard-list';
import { CreditCard as CreditCardIcon } from '../../icons/credit-card';
import { Home as HomeIcon } from '../../icons/home';
import { LockClosed as LockClosedIcon } from '../../icons/lock-closed';
import { Mail as MailIcon } from '../../icons/mail';
import { MailOpen as MailOpenIcon } from '../../icons/mail-open';
import { Newspaper as NewspaperIcon } from '../../icons/newspaper';
import { OfficeBuilding as OfficeBuildingIcon } from '../../icons/office-building';
import { ReceiptTax as ReceiptTaxIcon } from '../../icons/receipt-tax';
import { Selector as SelectorIcon } from '../../icons/selector';
import { Share as ShareIcon } from '../../icons/share';
//import { ReportIcon as Reporte } from '../../icons/reports-icon';
import { DashboardIcon as DashboardIcon } from '../../icons/dashboard-icon';
import { DefendantIcon as DefendantIcon } from '../../icons/defendants-icon';
import { CitationIcon as CitationIcon } from '../../icons/citation-icon';
import { UsersIcon as UserI } from '../../icons/users-icon';
import { DocketsIcon as DocketsIcon } from '../../icons/dockets-icon';

import { Scrollbar } from '../scrollbar';
import { DashboardSidebarSection } from './dashboard-sidebar-section';
import {Image} from "@react-pdf/renderer";

const getSections = (t) => [
    {
        title: t('General'),
        items: [
            {
                title: t('Dashboard'),
                path: '/dashboard',
                icon: <Reporte fontSize="small"/>
            },
   //       {
   //           title: t('Citations'),
   //           path: '/citations',
   //           icon: <CitationIcon fontSize="small"/>
   //       },
   //       {
   //           title: t('Defendants'),
   //           path: '/defendants',
   //           icon: <DefendantIcon fontSize="small"/>
   //       },
   //       {
   //           title: t('Dockets'),
   //           path: '/dockets',
   //           icon: <DocketsIcon fontSize="small"/>
   //       },
   //       {
   //           title: t('Reports'),
   //           path: '/reports',
   //           icon: <ReportIcon fontSize="small"/>
   //       },
        {
            title: t('Users'),
            path: '/dashboard/user',
            icon: <UserI fontSize="small"/>
        },
        ]
    }
];


export const DashboardSidebar = (props) => {
  const { onClose, open } = props;
  const router = useRouter();
  const { t } = useTranslation();
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
            height  : '100%'
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
            <Box sx={{ p: 3 }}>
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
          <Divider
            sx={{
              borderColor: '#2D3748',
              my: 3
            }}
          />
          <Box sx={{ flexGrow: 1 }}>
            {sections.map((section) => (
              <DashboardSidebarSection
                key={section.title}
                path={router.asPath}
                sx={{
                  mt: 2,
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
      sx={{ zIndex: (theme) => theme.zIndex.appBar + 100 }}
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
