public class App {
    public static void main(String[] args) throws Exception {
        // System.out.println("Hello, World!");
        /*
         * System.out.println("Desde fuera");
         * {
         * System.out.println("Hello, World!");
         * }
         */

        /*
         * Variable:
         * Es un espacio de memoria que tiene asociado y puede contener un valor
         * cambiante
         * Declaracion:
         * Asignar el tipo de dato y el nombre de la variable sin darle un valor inicial
         * Definicion:
         * Asignar el tipo de dato y el nombre de la variable dandole o no un valor
         * inicial
         * Asignacion:
         * Darle un valor a la variable ya declarada
         */
        // tipos de datos enteros: byte(-128 al 127), short(-32768 al 32767), int, long
        // int = integer
        int numero = 5;
        byte numero2 = 5;
        // TIPOS DE DATOS REALES: float (6 decimale) y el double (15 decimales)
        double decimales = 5.12345678;
        // TIPOS DE DATO DE CARACTER
        char letra = 'a';
        // TIPO DE DATO BOLEANO
        boolean v = true;
        boolean f = false;
        //
        /*
         * String cadena = "Hola mundo";
         * System.out.println(numero);
         * System.out.println(decimales);
         * System.out.println(numero2);
         * System.out.println(letra);
         * System.out.println(v);
         * System.out.println(f);
         * System.out.println(cadena);
         */
        /*
         * TIPOS DE VARIABLES
         * Variables primitivas: Almacenan valores basicos
         * Variables de referencia: Almacenan direcciones de momoria que apuntan a
         * objetos
         */
        /*
         * ////////////////////////////
         */

       /*  String texto = "  Este es un texto  ";
        System.out.println(texto);

        int longitud = texto.length();
        System.out.println(longitud);

        char character = texto.charAt(0);
        System.out.println(character);

        String subCadena = texto.substring(6, 12); //longitud ,caracter
        System.out.println(subCadena);

        String minuscula = texto.toLowerCase();
        System.out.println(minuscula);

        String mayuscula = texto.toUpperCase();
        System.out.println(mayuscula);

        int indice = texto.indexOf("texto");
        System.out.println(indice);

        String reemplazado = texto.replace("texto", "reemplazado");
        System.out.println(reemplazado);

        boolean contiene = texto.contains("asignado");
        System.out.println("Tiene la palabra asignado: " + contiene);
        
        String sinEspacios = texto.trim();
        System.out.println(sinEspacios); */
        

        //OPERADORES: simbolos que sirver para hacer operaciones con variables o valores 
        //ARITMETICOS: +, -, *, /, %
/*         int a = 10;
        int b = 2;
        int suma = a + b;
        int resta = a - b;
        int multiplicacion = a * b;
        int division = a / b;
        int resto = a % b; // El resto nos puede servir por ejemplo para saber si un numero es par o impar
        System.out.println(suma);
        System.out.println(resta);
        System.out.println(multiplicacion);
        System.out.println(division);
        System.out.println(resto);

        double c = 10;
        double d = 3;
        double modulo = c / d;
        System.out.println(modulo);

        // ASIGNACION: =, +=, -=, *=, /=, %=
        int x = 4;
        x = 10;
        x += 5; // x = x + 5
        x -= 5; // x = x - 5
        x *= 5; // x = x * 5
        x /= 5; // x = x / 5
        x %= 5; // x = x % 5
        x++; // x = x + 1
        x--; // x = x - 1
        System.out.println(x); */


        //OPERADORES DE COMPARACION: >, <, >=, <=, ==, !=
        /* int a = 5;
        int b = 10;

        boolean esMayor = a > b;
        boolean esMenor = a < b;
        boolean esIgual = a == b;
        System.out.println(esMayor);
        System.out.println(esMenor);
        System.out.println(esIgual); */

        //OPERADORES LOGICOS: &&, ||, !
        /* boolean condicion1 = true;
        boolean condicion2 = false;

        boolean resultadoAND = condicion1 && condicion2; // Ambos deber ser positivos para que de true
        boolean resultadoOR = condicion1 || condicion2; // uno de los dos debe ser positivo para que de true
        boolean resultadoNOT = !condicion1; // lo puesto a lo que tenga asignado previamente */
        /* 
         * TABLA DE LA VERDAD
         * AND              OR                 NOT
         * | V | | F       | V  | F         | V | !F
         * V | V | F      V | V  | V          F | !V
         * F | F | F      F | V  | F
         */
        /* System.out.println(resultadoAND);
        System.out.println(resultadoOR);
        System.out.println(resultadoNOT); */


        //CONDICIONALES: if, else if, else
        int edad = 78;
        if (edad > 18 && edad <= 60) {
            System.out.println("Puedes entrar a la disco");
        } else if (edad > 60) {
            System.out.println("Eres muy viejo para entrar a la disco");
        } else if (edad == 18) {
            System.out.println("Puedes entrar a la disco, pero no puedes tomar");
        }else {
            System.out.println("No puedes entrar a la disco");
        }
        //2:02:40 //SWITCH
    }
}
