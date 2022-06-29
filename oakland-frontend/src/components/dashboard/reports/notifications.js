import { useState } from 'react';
import {
  Box,
  Button,
  Card,
  CardHeader,
  Divider,
  IconButton,
  List,
  ListItem,
  ListItemIcon,
  ListItemSecondaryAction,
  ListItemText,
  Typography
} from '@mui/material';
import { Cube as CubeIcon } from '../../../icons/cube';
import { ArrowRight as ArrowRightIcon } from '../../../icons/arrow-right';
import { Users as UsersIcon } from '../../../icons/users';
import { Cash as CashIcon } from '../../../icons/cash';
import { ActionsMenu } from '../../actions-menu';
import ArrowForwardIcon from '@mui/icons-material/ArrowForward';

export const Notifications = () => {
  const [range, setRange] = useState('Last Month');

  const ranges = [
    {
      label: 'Last 7 days',
      onClick: () => { setRange('Last 7 Days'); }
    },
    {
      label: 'Last Month',
      onClick: () => { setRange('Last Month'); }
    },
    {
      label: 'Last Year',
      onClick: () => { setRange('Last Year'); }
    }
  ];

  return (
    <Card>
      <CardHeader
        action={(
          <ActionsMenu
            actions={ranges}
            label={range}
            size="small"
            variant="text"
          />
        )}
        title="Notifications"
      />
      <Divider />
      <List>
        <ListItem divider>
          <ListItemIcon>
            <CubeIcon sx={{ color: 'text.secondary' }} />
          </ListItemIcon>
          <ListItemText
            primary={(
              <Typography
                color="inherit"
                variant="body2"
              >
                <Typography
                  color="inherit"
                  component="span"
                  variant="subtitle2"
                >
                  3 pending orders
                </Typography>
                {' '}
                needs your attention.
              </Typography>
            )}
          />
          <ListItemSecondaryAction>
            <IconButton size="small">
              <ArrowRightIcon fontSize="small" />
            </IconButton>
          </ListItemSecondaryAction>
        </ListItem>
        <ListItem divider>
          <ListItemIcon>
            <UsersIcon sx={{ color: 'text.secondary' }} />
          </ListItemIcon>
          <ListItemText
            primary={(
              <Typography
                color="inherit"
                variant="body2"
              >
                <Typography
                  color="inherit"
                  component="span"
                  variant="subtitle2"
                >
                  1 team notes
                </Typography>
                {' '}
                at the
                {' '}
                <Typography
                  color="inherit"
                  component="span"
                  variant="subtitle2"
                >
                  Natalie Rusell.
                </Typography>
              </Typography>
            )}
          />
          <ListItemSecondaryAction>
            <IconButton size="small">
              <ArrowRightIcon fontSize="small" />
            </IconButton>
          </ListItemSecondaryAction>
        </ListItem>
        <ListItem>
          <ListItemIcon>
            <CashIcon sx={{ color: 'text.secondary' }} />
          </ListItemIcon>
          <ListItemText
            primary={(
              <Typography
                color="inherit"
                variant="body2"
              >
                <Typography
                  color="inherit"
                  component="span"
                  variant="subtitle2"
                >
                  3 pending transactions
                </Typography>
                {' '}
                needs your attention.
              </Typography>
            )}
          />
          <ListItemSecondaryAction>
            <IconButton size="small">
              <ArrowRightIcon fontSize="small" />
            </IconButton>
          </ListItemSecondaryAction>
        </ListItem>
      </List>
      <Divider />
      <Box
        sx={{
          backgroundColor: (theme) => theme.palette.mode == 'light' ? 'neutral.50' : 'neutral.900',
          py: 1,
          px: 3
        }}
      >
        <Button
          endIcon={(
            <ArrowForwardIcon />
          )}
        >
          See all notifications
        </Button>
      </Box>
    </Card>
  );
};
