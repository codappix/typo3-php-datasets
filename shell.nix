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
      composer install --prefer-dist --no-progress --working-dir="$PROJECT_ROOT"
    '';
  };

in pkgs.mkShell {
  name = "TYPO3 PHP Datasets";
  buildInputs = [
    projectInstall
    phpWithXdebug
    composer
    pkgs.parallel
  ];

  shellHook = ''
    export PROJECT_ROOT="$(pwd)"

    export typo3DatabaseDriver=pdo_sqlite
  '';
}
