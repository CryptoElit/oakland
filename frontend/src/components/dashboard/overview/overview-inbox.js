import { formatDistanceToNowStrict, subHours, subMinutes } from 'date-fns';
import {
  Avatar,
  Box,
  Button,
  Card,
  CardActions,
  CardHeader,
  Divider,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Typography
} from '@mui/material';

const messages = [
  {
    id: 'b91cbe81ee3efefba6b915a7',
    content: 'Hello, we spoke earlier on the phone',
    date: subMinutes(new Date(), 2),
    senderAvatar: '/static/mock-images/avatars/avatar-alcides_antonio.png',
    senderName: 'Heinz Gericke',
    unread: true
  },
  {
    id: 'de0eb1ac517aae1aa57c0b7e',
    content: 'What equipment do we need for the next job?',
    date: subMinutes(new Date(), 56),
    senderAvatar: '/static/mock-images/avatars/avatar-marcus_finn.png',
    senderName: 'Joseph Hart',
    unread: false
  },
  {
    id: '38e2b0942c90d0ad724e6f40',
    content: 'Ek bennodig hulp by die fabriek ASAP!',
    date: subHours(subMinutes(new Date(), 23), 3),
    senderAvatar: '/static/mock-images/avatars/avatar-carson_darrin.png',
    senderName: 'Hugo van Niekerk',
    unread: false
  },
  {
    id: '467505f3356f25a69f4c4890',
    content: 'Ek het met die Hoof van Verkope by Clover vandag n vergedering gehad.',
    date: subHours(subMinutes(new Date(), 6), 8),
    senderAvatar: '/static/mock-images/avatars/avatar-fran_perez.png',
    senderName: 'Michael Collins',
    unread: false
  },
  {
    id: '7e6af808e801a8361ce4cf8b',
    content: 'Need more information about current campaigns',
    date: subHours(subMinutes(new Date(), 18), 10),
    senderAvatar: '/static/mock-images/avatars/avatar-jie_yan_song.png',
    senderName: 'Henk van Zyl',
    unread: false
  }
];

export const OverviewInbox = (props) => (
  <Card {...props}>
    <CardHeader title="Inbox" />
    <Divider />
    <List disablePadding>
      {messages.map((message, index) => (
        <ListItem
          divider={index + 1 < messages.length}
          key={message.id}
        >
          <ListItemAvatar>
            <Avatar src={message.senderAvatar} />
          </ListItemAvatar>
          <ListItemText
            disableTypography
            primary={(
              <Box
                sx={{
                  alignItems: 'center',
                  display: 'flex'
                }}
              >
                <Typography variant="subtitle2">
                  {message.senderName}
                </Typography>
                {message.unread && (
                  <Box
                    sx={{
                      backgroundColor: 'primary.main',
                      borderRadius: '50%',
                      height: 8,
                      ml: 1,
                      width: 8
                    }}
                  />
                )}
              </Box>
            )}
            secondary={(
              <Typography
                color="textSecondary"
                variant="body2"
              >
                {message.content}
              </Typography>
            )}
            sx={{ pr: 2 }}
          />
          <Typography
            color="textSecondary"
            sx={{ whiteSpace: 'nowrap' }}
            variant="caption"
          >
            {formatDistanceToNowStrict(message.date, { addSuffix: true })}
          </Typography>
        </ListItem>
      ))}
    </List>
    <Divider />
    <CardActions>
      <Button>
        Go to chat
      </Button>
    </CardActions>
  </Card>
);
