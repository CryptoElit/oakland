import {useState} from 'react';
import PropTypes from 'prop-types';
import {styled} from '@mui/material/styles';



const MainLayoutRoot = styled('div')(({ theme }) => ({
  backgroundColor: theme.palette.background.default,
  height: '100%',
  paddingTop: 64
}));

export const MainLayout = ({ children }) => {

  return (
    <MainLayoutRoot>
      {children}
    </MainLayoutRoot>
  );
};

MainLayout.propTypes = {
  children: PropTypes.node
};
