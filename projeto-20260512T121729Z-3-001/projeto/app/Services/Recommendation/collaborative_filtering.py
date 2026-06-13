import numpy as np
import scipy.sparse as sp

class MatrixFactorization:
    def __init__(self, model="MF", embedding_dim=10, init="random", bias=True, max_iter=20,
                 regularization=0.02, learning_rate=0.005, seed=42, verbose=True,
                 lr_decay=0.9, early_stopping=5, eval_freq=1):
        """Matrix Factorization for Recommender Systems.

        Parameters
        ----------
        model : `str` (optional, default "MF")
            Model to train. Let's or A (12)
        Let is define mapping function and optimization loss optimization setup for standard matrix computation (which matrix factorization uses and is calculated element-wise to keep simple for standard sparse implementation). Default output is linear regression with regularized weight decay. Explicit target function value `y` or objective function values `y_train` parameters and `optimizer` can be passed to fit.

        Attributes
        ----------
        self.w : `np.array` (size: input_dim)
            trained weight vector
        self.b : `float`
            trained bias parameter
        self.pred : `np.array` (size: matrix_size, embedding_dim)
            trained latent factors for user/item embeddings
        self.loss : `list`
            trained loss history (on training, to check convergence)
        """
        self.model = model
        self.embedding_dim = embedding_dim
        self.init = init
        self.bias = bias
        self.iter = max_iter
        self.regularization = regularization
        self.learning_rate = learning_rate
        self.seed = seed
        self.verbose = verbose
        
        # for dynamic learning rate schedule
        self.lr_decay = lr_decay
        self.val = None
        self.early_stopping = early_stopping
        self.eval_freq = eval_freq
        
    def fit(self, train, y_train=None, val=None, y_val=None, **kwargs):
        """Fit model.

        Parameters
        ----------
        train :  `scipy.sparse.coo_matrix` or `tuple` (x_train_idx, y_train)
            if `coo_matrix` it represents the target matrix.
            if `tuple` it represents coordinates of non-zeros (x_train_idx, y_train).
        y_train : `np.array` (optional, default `None`)
            target vector
        val : `scipy.sparse.coo_matrix` or `tuple` (x_val_idx, y_val)
            if `coo_matrix` it represents the target matrix.
            if `tuple` it represents coordinates of non-zeros (x_val_idx, y_val).
        y_val : `np.array` (optional, default `None`)
            target vector for validation
        """
        
        # Initialize training data
        if type(train) is tuple:
            # Tuple input (x_idx, y)
            x_train_idx, y_train = train
        else:
            # coo_matrix input
            x_train_idx = np.vstack((train.row, train.col)).T
            y_train = train.data
            
        num_samples = len(y_train)
        
        # Calculate matrix shape
        n_users = np.max(x_train_idx[:,0]) + 1
        n_items = np.max(x_train_idx[:,1]) + 1
        
        if val is not None:
            if type(val) is tuple:
                x_val_idx, y_val = val
            else:
                x_val_idx = np.vstack((val.row, val.col)).T
                y_val = val.data
            n_users = max(n_users, np.max(x_val_idx[:,0]) + 1)
            n_items = max(n_items, np.max(x_val_idx[:,1]) + 1)
            
        self.n_users = n_users
        self.n_items = n_items
            
        # Initialize parameters
        np.random.seed(self.seed)
        
        # User and Item Latent Vectors
        self.P = np.random.normal(scale=1.0/self.embedding_dim, size=(n_users, self.embedding_dim))
        self.Q = np.random.normal(scale=1.0/self.embedding_dim, size=(n_items, self.embedding_dim))
        
        # Biases
        if self.bias:
            self.mu = np.mean(y_train)
            self.bu = np.zeros(n_users)
            self.bi = np.zeros(n_items)
        else:
            self.mu = 0.0
            self.bu = np.zeros(n_users)
            self.bi = np.zeros(n_items)
            
        self.loss = []
        self.val_loss = []
        
        best_val_rmse = float('inf')
        patience_counter = 0
        best_P = None
        best_Q = None
        best_bu = None
        best_bi = None
        
        lr = self.learning_rate
        
        for epoch in range(self.iter):
            # Shuffle indices
            shuffled_idx = np.random.permutation(num_samples)
            
            # Mini-batch or SGD step
            for idx in shuffled_idx:
                u = x_train_idx[idx, 0]
                i = x_train_idx[idx, 1]
                y_true = y_train[idx]
                
                # Predict
                y_pred = self.mu + self.bu[u] + self.bi[i] + np.dot(self.P[u], self.Q[i])
                
                # Compute error
                error = y_true - y_pred
                
                # Update biases
                if self.bias:
                    self.bu[u] += lr * (error - self.regularization * self.bu[u])
                    self.bi[i] += lr * (error - self.regularization * self.bi[i])
                
                # Update latent vectors
                p_temp = self.P[u].copy()
                self.P[u] += lr * (error * self.Q[i] - self.regularization * self.P[u])
                self.Q[i] += lr * (error * p_temp - self.regularization * self.Q[i])
                
            # Epoch evaluation
            if (epoch + 1) % self.eval_freq == 0 or epoch == 0:
                # Compute train RMSE
                train_preds = self._predict_batch(x_train_idx)
                train_rmse = np.sqrt(np.mean((y_train - train_preds) ** 2))
                self.loss.append(train_rmse)
                
                if val is not None:
                    val_preds = self._predict_batch(x_val_idx)
                    val_rmse = np.sqrt(np.mean((y_val - val_preds) ** 2))
                    self.val_loss.append(val_rmse)
                    
                    if self.verbose:
                        print(f"Epoch {epoch+1}/{self.iter} - Train RMSE: {train_rmse:.4f} - Val RMSE: {val_rmse:.4f}")
                        
                    # Early stopping check
                    if self.early_stopping:
                        if val_rmse < best_val_rmse:
                            best_val_rmse = val_rmse
                            patience_counter = 0
                            # Save best parameters
                            best_P = self.P.copy()
                            best_Q = self.Q.copy()
                            best_bu = self.bu.copy()
                            best_bi = self.bi.copy()
                        else:
                            patience_counter += 1
                            if patience_counter >= self.early_stopping:
                                if self.verbose:
                                    print(f"Early stopping at epoch {epoch+1}. Best Val RMSE: {best_val_rmse:.4f}")
                                # Restore best parameters
                                self.P = best_P
                                self.Q = best_Q
                                self.bu = best_bu
                                self.bi = best_bi
                                break
                else:
                    if self.verbose:
                        print(f"Epoch {epoch+1}/{self.iter} - Train RMSE: {train_rmse:.4f}")
                        
            # Learning rate decay
            lr *= self.lr_decay
            
    def _predict_batch(self, x_idx):
        """Internal batch prediction."""
        preds = np.zeros(len(x_idx))
        for idx in range(len(x_idx)):
            u = x_idx[idx, 0]
            i = x_idx[idx, 1]
            # Handle out of bounds/new users or items
            if u >= self.n_users or i >= self.n_items:
                preds[idx] = self.mu
            else:
                preds[idx] = self.mu + self.bu[u] + self.bi[i] + np.dot(self.P[u], self.Q[i])
        return preds
        
    def predict(self, x_idx):
        """Predict target values.

        Parameters
        ----------
        x_idx : `np.array` (size: num_samples, 2)
            user and item indices.
        """
        return self._predict_batch(x_idx)
