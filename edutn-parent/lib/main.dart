import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:easy_localization/easy_localization.dart';
import 'package:hive_flutter/hive_flutter.dart';

import 'core/api/api_client.dart';
import 'core/auth/auth_bloc.dart';
import 'core/theme/app_theme.dart';
import 'core/localization/supported_locales.dart';
import 'features/home/presentation/pages/home_page.dart';
import 'features/auth/presentation/pages/login_page.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await EasyLocalization.ensureInitialized();
  await Hive.initFlutter();

  runApp(
    EasyLocalization(
      supportedLocales: SupportedLocales.all,
      path: 'assets/translations',
      fallbackLocale: SupportedLocales.french,
      startLocale: SupportedLocales.french,
      child: const EduttnParentApp(),
    ),
  );
}

class EduttnParentApp extends StatelessWidget {
  const EduttnParentApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(
          create: (context) => AuthBloc(
            apiClient: ApiClient(),
          )..add(AuthCheckRequested()),
        ),
      ],
      child: MaterialApp(
        title: 'EDUTN Parent',
        debugShowCheckedModeBanner: false,
        localizationsDelegates: context.localizationDelegates,
        supportedLocales: context.supportedLocales,
        locale: context.locale,
        theme: AppTheme.lightTheme,
        darkTheme: AppTheme.darkTheme,
        home: BlocBuilder<AuthBloc, AuthState>(
          builder: (context, state) {
            if (state is AuthAuthenticated) {
              return const HomePage();
            }
            return const LoginPage();
          },
        ),
      ),
    );
  }
}
