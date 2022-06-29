import { useState } from 'react';
import dynamic from 'next/dynamic';
import {
  Box,
  Card,
  CardContent,
  CardHeader,
  Divider,
  List,
  ListItem,
  ListSubheader,
  Typography
} from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { ActionsMenu } from '../../actions-menu';
import { StatusBadge } from '../../status-badge';

const Chart = dynamic(() => import('react-apexcharts'), { ssr: false });

const series = [
  {
    color: '#64B6F7',
    data: 60,
    name: 'Complete'
  },
  {
    color: '#F06191',
    data: 15,
    name: 'Pending'
  },
  {
    color: '#7BC67E',
    data: 15,
    name: 'Cancelled'
  },
  {
    color: '#FFB547',
    data: 10,
    name: 'Refunded'
  }
];

export const OrdersOverview = (props) => {
  const theme = useTheme();
  const [range, setRange] = useState('Last 7 days');

  const ranges = [
    {
      label: 'Last 7 days',
      onClick: () => { setRange('Last 7 days'); }
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

  const chartOptions = {
    chart: {
      background: 'transparent'
    },
    colors: series.map((item) => item.color),
    dataLabels: {
      enabled: false
    },
    labels: series.map((item) => item.name),
    legend: {
      show: false
    },
    stroke: {
      show: false
    },
    theme: {
      mode: theme.palette.mode
    }
  };

  const chartSeries = series.map((item) => item.data);

  return (
    <Card {...props}>
      <CardHeader
        action={(
          <ActionsMenu
            actions={ranges}
            label={range}
            size="small"
            variant="text"
          />
        )}
        title="Orders Overview"
      />
      <Divider />
      <CardContent>
        <Chart
          height={200}
          options={chartOptions}
          series={chartSeries}
          type="donut"
        />
        <List>
          <ListSubheader
            disableGutters
            component="div"
            sx={{
              alignItems: 'center',
              display: 'flex',
              py: 1
            }}
          >
            <Typography
              color="textPrimary"
              variant="subtitle2"
            >
              Total
            </Typography>
            <Box sx={{ flexGrow: 1 }} />
            <Typography
              color="textPrimary"
              variant="subtitle2"
            >
              {series.reduce((acc, currentValue) => acc + currentValue.data, 0)}
            </Typography>
          </ListSubheader>
          <Divider />
          {series.map((item, index) => (
            <ListItem
              disableGutters
              divider={series.length > index + 1}
              key={item.name}
              sx={{ display: 'flex' }}
            >
              <StatusBadge
                color={item.color}
                sx={{ mr: 1 }}
              />
              <Typography
                color="textSecondary"
                variant="body2"
              >
                {item.name}
              </Typography>
              <Box sx={{ flexGrow: 1 }} />
              <Typography
                color="textSecondary"
                variant="body2"
              >
                {item.data}
              </Typography>
            </ListItem>
          ))}
        </List>
      </CardContent>
    </Card>
  );
};
