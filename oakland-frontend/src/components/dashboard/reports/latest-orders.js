import PropTypes from 'prop-types';
import NextLink from 'next/link';
import { Button, Card, CardHeader, Divider } from '@mui/material';
import { OrderPreviewItem } from '../order/order-preview-item';
import { OrderPreviewList } from '../order/order-preview-list';

export const LatestOrders = (props) => {
  const { orders } = props;

  return (
    <Card

      {...props}>
      <CardHeader
        action={(
          <NextLink
            href="/dashboard/orders"
            passHref
          >
            <Button
              color="primary"
              component="a"
              variant="text"
            >
              Go to orders
            </Button>
          </NextLink>
        )}
        title="Latest Orders"
      />
      <Divider />
      <OrderPreviewList>
        {orders.map((order, index) => (
          <OrderPreviewItem
            divider={orders.length > index + 1}
            key={order.id}
            order={order}
          />
        ))}
      </OrderPreviewList>
    </Card>
  );
};

LatestOrders.propTypes = {
  orders: PropTypes.array
};
