import {createTheme as createMuiTheme, responsiveFontSizes} from '@mui/material/styles';
import {baseThemeOptions} from './base-theme-options';
import {lightThemeOptions} from './light-theme-options';

export const createTheme = (config) => {
  let theme = createMuiTheme(baseThemeOptions,
      lightThemeOptions,
    {
      direction: config.direction
    });

  if (config.responsiveFontSizes) {
    theme = responsiveFontSizes(theme);
  }

  return theme;
};
