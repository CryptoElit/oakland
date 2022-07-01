import { useEffect } from 'react';
import { MainLayout } from '../components/main-layout';
import { gtm } from '../lib/gtm';
import Login from "./authentication/login";

const Home = () => {
  useEffect(() => {
    gtm.push({ event: 'page_view' });
  }, []);

  return (
    <>
		<Login />
    </>
  );
};

Home.getLayout = (page) => (
  <MainLayout>
    {page}
  </MainLayout>
);

export default Home;
