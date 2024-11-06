// vite.config.mjs
import { defineConfig } from "file:///C:/laragon/www/daliapp/node_modules/vite/dist/node/index.js";
import laravel from "file:///C:/laragon/www/daliapp/node_modules/laravel-vite-plugin/dist/index.js";
import html from "file:///C:/laragon/www/daliapp/node_modules/@rollup/plugin-html/dist/es/index.js";
import { glob } from "file:///C:/laragon/www/daliapp/node_modules/glob/dist/esm/index.js";
function GetFilesArray(query) {
  return glob.sync(query);
}
var pageJsFiles = GetFilesArray("resources/assets/js/*.js");
var pageCssFiles = GetFilesArray("resources/assets/css/*.css");
var vendorJsFiles = GetFilesArray("resources/assets/vendor/js/*.js");
var LibsJsFiles = GetFilesArray("resources/assets/vendor/libs/**/*.js");
var CoreScssFiles = GetFilesArray("resources/assets/vendor/scss/**/!(_)*.scss");
var LibsScssFiles = GetFilesArray("resources/assets/vendor/libs/**/!(_)*.scss");
var LibsCssFiles = GetFilesArray("resources/assets/vendor/libs/**/*.css");
var FontsScssFiles = GetFilesArray("resources/assets/vendor/fonts/!(_)*.scss");
function libsWindowAssignment() {
  return {
    name: "libsWindowAssignment",
    transform(src, id) {
      if (id.includes("jkanban.js")) {
        return src.replace("this.jKanban", "window.jKanban");
      } else if (id.includes("vfs_fonts")) {
        return src.replaceAll("this.pdfMake", "window.pdfMake");
      }
    }
  };
}
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        "resources/js/app.js",
        ...pageJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        "resources/js/laravel-user-management.js",
        // Processing Laravel User Management CRUD JS File
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles,
        ...pageCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment()
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcubWpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyJjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZGlybmFtZSA9IFwiQzpcXFxcbGFyYWdvblxcXFx3d3dcXFxcY2hlbGF0b2FwcFwiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9maWxlbmFtZSA9IFwiQzpcXFxcbGFyYWdvblxcXFx3d3dcXFxcY2hlbGF0b2FwcFxcXFx2aXRlLmNvbmZpZy5tanNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfaW1wb3J0X21ldGFfdXJsID0gXCJmaWxlOi8vL0M6L2xhcmFnb24vd3d3L2NoZWxhdG9hcHAvdml0ZS5jb25maWcubWpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCBodG1sIGZyb20gJ0Byb2xsdXAvcGx1Z2luLWh0bWwnO1xuaW1wb3J0IHsgZ2xvYiB9IGZyb20gJ2dsb2InO1xuXG4vKipcbiAqIEdldCBGaWxlcyBmcm9tIGEgZGlyZWN0b3J5XG4gKiBAcGFyYW0ge3N0cmluZ30gcXVlcnlcbiAqIEByZXR1cm5zIGFycmF5XG4gKi9cbmZ1bmN0aW9uIEdldEZpbGVzQXJyYXkocXVlcnkpIHtcbiAgcmV0dXJuIGdsb2Iuc3luYyhxdWVyeSk7XG59XG4vKipcbiAqIEpzIEZpbGVzXG4gKi9cbi8vIFBhZ2UgSlMgRmlsZXNcbmNvbnN0IHBhZ2VKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy9qcy8qLmpzJyk7XG5cbi8vIFBhZ2UgQ1NTIEZpbGVzXG5jb25zdCBwYWdlQ3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL2Nzcy8qLmNzcycpO1xuXG4vLyBQcm9jZXNzaW5nIFZlbmRvciBKUyBGaWxlc1xuY29uc3QgdmVuZG9ySnNGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2pzLyouanMnKTtcblxuLy8gUHJvY2Vzc2luZyBMaWJzIEpTIEZpbGVzXG5jb25zdCBMaWJzSnNGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2xpYnMvKiovKi5qcycpO1xuXG4vKipcbiAqIFNjc3MgRmlsZXNcbiAqL1xuLy8gUHJvY2Vzc2luZyBDb3JlLCBUaGVtZXMgJiBQYWdlcyBTY3NzIEZpbGVzXG5jb25zdCBDb3JlU2Nzc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3Ivc2Nzcy8qKi8hKF8pKi5zY3NzJyk7XG5cbi8vIFByb2Nlc3NpbmcgTGlicyBTY3NzICYgQ3NzIEZpbGVzXG5jb25zdCBMaWJzU2Nzc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3IvbGlicy8qKi8hKF8pKi5zY3NzJyk7XG5jb25zdCBMaWJzQ3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9saWJzLyoqLyouY3NzJyk7XG5cbi8vIFByb2Nlc3NpbmcgRm9udHMgU2NzcyBGaWxlc1xuY29uc3QgRm9udHNTY3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9mb250cy8hKF8pKi5zY3NzJyk7XG5cbi8vIFByb2Nlc3NpbmcgV2luZG93IEFzc2lnbm1lbnQgZm9yIExpYnMgbGlrZSBqS2FuYmFuLCBwZGZNYWtlXG5mdW5jdGlvbiBsaWJzV2luZG93QXNzaWdubWVudCgpIHtcbiAgcmV0dXJuIHtcbiAgICBuYW1lOiAnbGlic1dpbmRvd0Fzc2lnbm1lbnQnLFxuXG4gICAgdHJhbnNmb3JtKHNyYywgaWQpIHtcbiAgICAgIGlmIChpZC5pbmNsdWRlcygnamthbmJhbi5qcycpKSB7XG4gICAgICAgIHJldHVybiBzcmMucmVwbGFjZSgndGhpcy5qS2FuYmFuJywgJ3dpbmRvdy5qS2FuYmFuJyk7XG4gICAgICB9IGVsc2UgaWYgKGlkLmluY2x1ZGVzKCd2ZnNfZm9udHMnKSkge1xuICAgICAgICByZXR1cm4gc3JjLnJlcGxhY2VBbGwoJ3RoaXMucGRmTWFrZScsICd3aW5kb3cucGRmTWFrZScpO1xuICAgICAgfVxuICAgIH1cbiAgfTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKHtcbiAgcGx1Z2luczogW1xuICAgIGxhcmF2ZWwoe1xuICAgICAgaW5wdXQ6IFtcbiAgICAgICAgJ3Jlc291cmNlcy9jc3MvYXBwLmNzcycsXG4gICAgICAgICdyZXNvdXJjZXMvanMvYXBwLmpzJyxcbiAgICAgICAgLi4ucGFnZUpzRmlsZXMsXG4gICAgICAgIC4uLnZlbmRvckpzRmlsZXMsXG4gICAgICAgIC4uLkxpYnNKc0ZpbGVzLFxuICAgICAgICAncmVzb3VyY2VzL2pzL2xhcmF2ZWwtdXNlci1tYW5hZ2VtZW50LmpzJywgLy8gUHJvY2Vzc2luZyBMYXJhdmVsIFVzZXIgTWFuYWdlbWVudCBDUlVEIEpTIEZpbGVcbiAgICAgICAgLi4uQ29yZVNjc3NGaWxlcyxcbiAgICAgICAgLi4uTGlic1Njc3NGaWxlcyxcbiAgICAgICAgLi4uTGlic0Nzc0ZpbGVzLFxuICAgICAgICAuLi5Gb250c1Njc3NGaWxlcyxcbiAgICAgICAgLi4ucGFnZUNzc0ZpbGVzXG4gICAgICBdLFxuICAgICAgcmVmcmVzaDogdHJ1ZVxuICAgIH0pLFxuICAgIGh0bWwoKSxcbiAgICBsaWJzV2luZG93QXNzaWdubWVudCgpXG4gIF1cbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUF1USxTQUFTLG9CQUFvQjtBQUNwUyxPQUFPLGFBQWE7QUFDcEIsT0FBTyxVQUFVO0FBQ2pCLFNBQVMsWUFBWTtBQU9yQixTQUFTLGNBQWMsT0FBTztBQUM1QixTQUFPLEtBQUssS0FBSyxLQUFLO0FBQ3hCO0FBS0EsSUFBTSxjQUFjLGNBQWMsMEJBQTBCO0FBRzVELElBQU0sZUFBZSxjQUFjLDRCQUE0QjtBQUcvRCxJQUFNLGdCQUFnQixjQUFjLGlDQUFpQztBQUdyRSxJQUFNLGNBQWMsY0FBYyxzQ0FBc0M7QUFNeEUsSUFBTSxnQkFBZ0IsY0FBYyw0Q0FBNEM7QUFHaEYsSUFBTSxnQkFBZ0IsY0FBYyw0Q0FBNEM7QUFDaEYsSUFBTSxlQUFlLGNBQWMsdUNBQXVDO0FBRzFFLElBQU0saUJBQWlCLGNBQWMsMENBQTBDO0FBRy9FLFNBQVMsdUJBQXVCO0FBQzlCLFNBQU87QUFBQSxJQUNMLE1BQU07QUFBQSxJQUVOLFVBQVUsS0FBSyxJQUFJO0FBQ2pCLFVBQUksR0FBRyxTQUFTLFlBQVksR0FBRztBQUM3QixlQUFPLElBQUksUUFBUSxnQkFBZ0IsZ0JBQWdCO0FBQUEsTUFDckQsV0FBVyxHQUFHLFNBQVMsV0FBVyxHQUFHO0FBQ25DLGVBQU8sSUFBSSxXQUFXLGdCQUFnQixnQkFBZ0I7QUFBQSxNQUN4RDtBQUFBLElBQ0Y7QUFBQSxFQUNGO0FBQ0Y7QUFFQSxJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUMxQixTQUFTO0FBQUEsSUFDUCxRQUFRO0FBQUEsTUFDTixPQUFPO0FBQUEsUUFDTDtBQUFBLFFBQ0E7QUFBQSxRQUNBLEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxRQUNIO0FBQUE7QUFBQSxRQUNBLEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxRQUNILEdBQUc7QUFBQSxNQUNMO0FBQUEsTUFDQSxTQUFTO0FBQUEsSUFDWCxDQUFDO0FBQUEsSUFDRCxLQUFLO0FBQUEsSUFDTCxxQkFBcUI7QUFBQSxFQUN2QjtBQUNGLENBQUM7IiwKICAibmFtZXMiOiBbXQp9Cg==
