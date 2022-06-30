import { useRouter } from 'next/router';
import * as Yup from 'yup';
import { useFormik } from 'formik';
import { Alert, Box, Button, FormHelperText, TextField } from '@mui/material';
import { useAuth } from '../../hooks/use-auth';
import { useMounted } from '../../hooks/use-mounted';

export const JWTLogin = (props) => {

   function postData(url = '', data = {}) {
     // Default options are marked with *
     fetch('http://localhost:7878/product-images', {
       method: 'POST', // *GET, POST, PUT, DELETE, etc.
       mode: 'cors', // no-cors, *cors, same-origin
       cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
       credentials: 'same-origin', // include, *same-origin, omit
       headers: {
         'Content-Type': 'application/json'
         // 'Content-Type': 'application/x-www-form-urlencoded',
       },
       redirect: 'follow', // manual, *follow, error
       referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
     });
   }
  postData()
  return (
    <div></div>
  );
};
