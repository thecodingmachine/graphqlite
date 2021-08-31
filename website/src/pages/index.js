import Layout from "@theme/Layout";
import React from "react";

export default () => {
  if (typeof window !== "undefined") {
      window.location.href = "/docs";
  }
  
  // Rendering the Layout helps keep the page from jumping, it takes a minute for the
  // location to change
  return <Layout />;
};
