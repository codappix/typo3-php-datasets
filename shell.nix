{
  pkgs ? import <nixpkgs> { }
  ,phps ? import <phps>
}:

let
  php = phps.packages.x86_64-linux.php81;
  inherit(php.packages) composer;

  phpWithXdebug = php.buildEnv {
    extensions = { enabled, all }: enabled ++ (with all; [
      xdebug
    ]);

    extraConfig = ''
      xdebug.mode = debug
    '';
  };

  projectInstall = pkgs.writeShellApplication {
    name = "project-install";
    runtimeInputs = [
      php
      composer
    ];
    text = ''
      composer update --prefer-dist --no-progress
    '';
  };
  projectCgl = pkgs.writeShellApplication {
    name = "project-cgl";
    runtimeInputs = [
      php
    ];
    text = ''
      ./vendor/bin/php-cs-fixer fix --diff
    '';
  };

in pkgs.mkShell {
  name = "TYPO3 PHP Datasets";
  buildInputs = [
    projectInstall
    projectCgl
    phpWithXdebug
    composer
  ];

  shellHook = ''
    export typo3DatabaseDriver=pdo_sqlite
  '';
}
