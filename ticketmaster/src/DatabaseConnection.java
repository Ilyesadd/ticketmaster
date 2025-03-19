import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DatabaseConnection {
    private static final String URL = "jdbc:mysql://localhost:8889/ticketmaster";
    private static final String USER = "root";
    private static final String PASSWORD = "root";

    public static Connection getConnection() {
        System.out.println("🔄 Tentative de connexion à la base de données...");

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            Connection conn = DriverManager.getConnection(URL, USER, PASSWORD);
            System.out.println("✅ Connexion réussie !");
            return conn;
        } catch (ClassNotFoundException e) {
            System.out.println("❌ Driver MySQL non trouvé !");
            e.printStackTrace();
            return null;
        } catch (SQLException e) {
            System.out.println("❌ Erreur de connexion à MySQL !");
            e.printStackTrace();
            return null;
        }
    }

    public static void main(String[] args) {
        Connection conn = getConnection();
        if (conn != null) {
            System.out.println("🎉 Tout est OK !");
        } else {
            System.out.println("🚨 La connexion a échoué !");
        }
    }
}
